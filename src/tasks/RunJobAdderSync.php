<?php

use BiffBangPow\JobAdderJobBoard\DataObjects\JobAd;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobAdderSyncRecord;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobCategory;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobConsultant;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobCountry;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobCurrency;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobLocation;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobSalaryFrequency;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobSubCategory;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobWorkType;
use GuzzleHttp\Exception\GuzzleException;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\Queries\SQLSelect;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\SiteConfig\SiteConfig;

class RunJobAdderSync extends BuildTask
{
    protected $title = 'Run Job Adder Sync';

    protected $description = 'Syncs jobs from Job Adder into the CMS';

    protected $enabled = true;

    /**
     * @var JobAdderAPIClient
     */
    private $apiClient;

    private $output = [];

    private $totalJobsSynced = 0;

    /**
     * RunJobAdderSync constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->apiClient = new JobAdderAPIClient();
    }

    /**
     * @param HTTPRequest $request
     * @throws GuzzleException
     * @throws ValidationException
     * @throws Exception
     */
    public function run($request)
    {
        $syncStarted = new DateTime();
        $this->syncJobAds();
        $this->cleanup();
        $syncRecord = JobAdderSyncRecord::create();
        $syncFinished = new DateTime();
        $syncRecord->Started = $syncStarted->format('Y-m-d H:i:s');
        $syncRecord->Finished = $syncFinished->format('Y-m-d H:i:s');
        $syncRecord->Output = $this->getOutputString();
        $syncRecord->write();
    }

    /**
     * @return bool
     * @throws ValidationException
     * @throws GuzzleException
     */
    public function syncJobAds()
    {
        $siteConfig = SiteConfig::current_site_config();

        if (
            $siteConfig->JobAdderJobBoardAccessToken === null ||
            $siteConfig->JobAdderJobBoardAccessToken === '' ||
            $siteConfig->JobAdderJobBoardJobBoardID === null ||
            $siteConfig->JobAdderJobBoardJobBoardID === ''
        ) {
            $this->addOutput('No access token or job board ID, sync cannot run');
            return false;
        }

        $this->apiClient->refreshAccessToken();
        $this->addOutput('Access token refreshed');

        $jobAds = $this->apiClient->getJobAds();

        $this->totalJobsSynced = 0;

        foreach ($jobAds as $jobAd) {
            $this->syncJobAd($jobAd);
        }

        $this->addOutput('Sync complete, synced ' . $this->totalJobsSynced . ' total jobs');

        return true;
    }

    /**
     * @throws Exception
     */
    public function cleanup()
    {
        $this->addOutput('Cleaning up');
        $this->cleanupExpiredJobAds();
        $this->cleanupDeletedJobAds();
        $this->cleanupUnusedDataobjects();
    }

    /**
     * @return array
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @return string
     */
    public function getOutputString()
    {
        $output = '';
        foreach ($this->getOutput() as $outputLine) {
            $output .= $outputLine['Timestamp'] . ' - ' . $outputLine['Message'] . PHP_EOL;
        }
        return $output;
    }

    /**
     * @param $jobAd
     * @throws ValidationException
     */
    public function syncJobAd($jobAd)
    {
        $adId = $jobAd['adId'];

        $adDataFromJobBoard = $this->apiClient->getJobAdFromJobBoard($adId);

        $jobAd = JobAd::get()->filter(['JobAdderId' => $adId])->first();

        if ($jobAd === null) {
            $jobAd = new JobAd();
        }

        $this->syncJobAdBasicData($adDataFromJobBoard, $jobAd);
        $this->syncLinksData($adDataFromJobBoard, $jobAd);

        if (isset($adDataFromJobBoard['portal']) || array_key_exists('portal', $adDataFromJobBoard)) {

            $portal = $adDataFromJobBoard['portal'];
            $this->syncJobAdPortalData($portal, $jobAd);

            if (isset($portal['fields']) || array_key_exists('fields', $portal)) {

                $fields = $portal['fields'];

                $this->syncJobAdFieldsData($fields, $jobAd);

            }

        }

        $adData = $this->apiClient->getJobAd($adId);
        $this->syncConsultantData($adData, $jobAd);

        $this->extend('updateSyncJobAd', $jobAd, $adDataFromJobBoard);

        $jobAd->LastEdited = date('Y-m-d H:i:s');
        $jobAd->write();
        $this->totalJobsSynced++;

        $this->addOutput('Synced job: ' . $jobAd->Title . ' (' . $jobAd->JobAdderReference . ')');
    }

    public function syncLinksData(array $adData, JobAd $jobAd)
    {
        if (isset($adData['links']) || array_key_exists('links', $adData)) {

            $links = $adData['links'];

            if (isset($links['ui']) || array_key_exists('ui', $links)) {

                $uiLinks = $links['ui'];

                if (isset($uiLinks['applications']) || array_key_exists('applications', $uiLinks)) {

                    $jobAd->ApplicationLink = $uiLinks['applications'];

                }

            }

        }
    }

    /**
     * @param array $adData
     * @param JobAd $jobAd
     * @throws GuzzleException
     * @throws ValidationException
     */
    public function syncConsultantData(array $adData, JobAd $jobAd)
    {
        if (isset($adData['owner']) || array_key_exists('owner', $adData)) {

            $owner = $adData['owner'];

            $consultantObject = JobConsultant::get()->filter(['JobAdderReference' => $owner['userId']])->first();

            if ($consultantObject === null) {
                $consultantObject = new JobConsultant();
            }

            if (isset($owner['userId']) || array_key_exists('userId', $owner)) {
                $consultantObject->JobAdderReference = $owner['userId'];
            }

            if (isset($owner['firstName']) || array_key_exists('firstName', $owner)) {
                $consultantObject->FirstName = $owner['firstName'];
            }

            if (isset($owner['lastName']) || array_key_exists('lastName', $owner)) {
                $consultantObject->LastName = $owner['lastName'];
            }

            if (isset($owner['position']) || array_key_exists('position', $owner)) {
                $consultantObject->Position = $owner['position'];
            }

            if (isset($owner['jobTitle']) || array_key_exists('jobTitle', $owner)) {
                $consultantObject->JobTitle = $owner['jobTitle'];
            }

            if (isset($owner['email']) || array_key_exists('email', $owner)) {
                $consultantObject->Email = $owner['email'];
            }

            if (isset($owner['phone']) || array_key_exists('phone', $owner)) {
                $consultantObject->Phone = $owner['phone'];
            }

            if (isset($owner['mobile']) || array_key_exists('mobile', $owner)) {
                $consultantObject->Mobile = $owner['mobile'];
            }

            if (isset($owner['links']) || array_key_exists('links', $owner)) {

                $links = $owner['links'];

                if (isset($links['photo']) || array_key_exists('photo', $links)) {
                    $consultantObject->PhotoURL = $links['photo'];
                }

            }

            $consultantPhotoResponse = $this->apiClient->getConsultantPhotoResponse($owner['userId']);

            if ($consultantPhotoResponse !== null) {
                sscanf($consultantPhotoResponse->getHeader('Content-Type')[0],"image/%s", $photoExtension);
                $filePath = sprintf('/consultant/%d.%s', $owner['userId'], $photoExtension);
                file_put_contents("public" . $filePath, $consultantPhotoResponse->getBody());
                $consultantObject->PhotoURL = $filePath;
            }

            $consultantObject->write();
            $jobAd->Consultant = $consultantObject;

        }
    }

    /**
     * @param array $adData
     * @param JobAd $jobAd
     */
    public function syncJobAdBasicData(array $adData, JobAd $jobAd)
    {
        if (isset($adData['title']) || array_key_exists('title', $adData)) {
            $jobAd->Title = $adData['title'];
        }

        if (isset($adData['adId']) || array_key_exists('adId', $adData)) {
            $jobAd->JobAdderId = $adData['adId'];
        }

        $jobAd->Slug = SiteTree::create()->generateURLSegment($jobAd->JobAdderId . '-' . $jobAd->Title);;

        if (isset($adData['reference']) || array_key_exists('reference', $adData)) {
            $jobAd->JobAdderReference = $adData['reference'];
        }

        if (isset($adData['summary']) || array_key_exists('summary', $adData)) {
            $jobAd->Summary = $adData['summary'];
        }

        if (isset($adData['bulletPoints']) || array_key_exists('bulletPoints', $adData)) {

            if (count($adData['bulletPoints']) > 0) {
                $bulletPointsHTML = '<ul>';

                foreach ($adData['bulletPoints'] as $bulletPoint) {
                    $bulletPointsHTML .= sprintf('<li>%s</li>', $bulletPoint);
                }

                $bulletPointsHTML .= '</ul>';

                $jobAd->BulletPoints = $bulletPointsHTML;
            }

        }

        if (isset($adData['description']) || array_key_exists('description', $adData)) {
            $jobAd->Description = $adData['description'];
        }

        if (isset($adData['postedAt']) || array_key_exists('postedAt', $adData)) {
            $jobAd->PostedAt = $adData['postedAt'];
        }

        if (isset($adData['updatedAt']) || array_key_exists('updatedAt', $adData)) {
            $jobAd->UpdatedAt = $adData['updatedAt'];
        }

        if (isset($adData['expiresAt']) || array_key_exists('expiresAt', $adData)) {
            $jobAd->ExpiresAt = $adData['expiresAt'];
        }

    }

    /**
     * @param array $portal
     * @param JobAd $jobAd
     */
    public function syncJobAdPortalData(array $portal, JobAd $jobAd)
    {
        if (isset($portal['hotJob']) || array_key_exists('hotJob', $portal)) {

            $jobAd->HotJob = $portal['hotJob'];

        }

        if (isset($portal['salary']) || array_key_exists('salary', $portal)) {

            $salary = $portal['salary'];

            if (isset($salary['ratePer']) || array_key_exists('ratePer', $salary)) {

                $jobAd->SalaryRatePer = $salary['ratePer'];

                $ratePer = $salary['ratePer'];

                $salaryFrequencyObject = JobSalaryFrequency::get()->filter(['Title' => $ratePer])->first();
                if ($salaryFrequencyObject === null) {
                    $salaryFrequencyObject = JobSalaryFrequency::create();
                    $salaryFrequencyObject->Title = $ratePer;
                    $salaryFrequencyObject->write();
                }
                $jobAd->SalaryFrequency = $salaryFrequencyObject;

            }

            if (isset($salary['rateLow']) || array_key_exists('rateLow', $salary)) {

                $jobAd->SalaryRateLow = $salary['rateLow'];

            }

            if (isset($salary['rateHigh']) || array_key_exists('rateHigh', $salary)) {

                $jobAd->SalaryRateHigh = $salary['rateHigh'];

            }

            $this->extend('syncJobAdPortalSalaryData', $jobAd, $salary);

        }

        $this->extend('syncJobAdPortalData', $jobAd, $portal);
    }

    /**
     * @param array $fields
     * @param JobAd $jobAd
     * @throws ValidationException
     */
    public function syncJobAdFieldsData(array $fields, JobAd $jobAd)
    {
        $displayLocation = $this->findFieldWithName($fields, 'Display Location');
        $jobAd->DisplayLocation = $displayLocation['value'];

        $displaySalary = $this->findFieldWithName($fields, 'Display Salary');
        $jobAd->DisplaySalary = $displaySalary['value'];

        $currency = $this->findFieldWithName($fields, 'Currency');
        if ($currency !== null) {
            $currencyObject = JobCurrency::get()->filter(['JobAdderReference' => $currency['valueId']])->first();
            if ($currencyObject === null) {
                $currencyObject = JobCurrency::create();
                $currencyObject->JobAdderReference = $currency['valueId'];
            }
            $currencyObject->Title = $currency['value'];
            $currencyObject->write();
            $jobAd->Currency = $currencyObject;
        } else {
            $jobAd->CurrencyID = 0;
        }

        $workType = $this->findFieldWithName($fields, 'Work Type');
        if ($workType !== null) {
            $workTypeObject = JobWorkType::get()->filter(['JobAdderReference' => $workType['valueId']])->first();
            if ($workTypeObject === null) {
                $workTypeObject = JobWorkType::create();
                $workTypeObject->JobAdderReference = $workType['valueId'];
            }
            $workTypeObject->Title = $workType['value'];
            $workTypeObject->write();
            $jobAd->WorkType = $workTypeObject;
        } else {
            $jobAd->WorkTypeID = 0;
        }

        $location = $this->findFieldWithName($fields, 'Location');
        if ($location !== null) {
            $locationObject = JobLocation::get()->filter(['JobAdderReference' => $location['valueId']])->first();
            if ($locationObject === null) {
                $locationObject = JobLocation::create();
                $locationObject->JobAdderReference = $location['valueId'];
            }
            $locationObject->Title = $location['value'];
            $this->extend('updateCreateLocation', $locationObject, $location);
            $locationObject->write();
            $jobAd->Location = $locationObject;
        } else {
            $jobAd->LocationID = 0;
        }

        $category = $this->findFieldWithName($fields, 'Category');
        if ($category !== null) {
            $categoryObject = JobCategory::get()->filter(['JobAdderReference' => $category['valueId']])->first();
            if ($categoryObject === null) {
                $categoryObject = JobCategory::create();
                $categoryObject->JobAdderReference = $category['valueId'];
            }
            $categoryObject->Title = $category['value'];
            $this->extend('updateCreateCategory', $categoryObject, $category);
            $categoryObject->write();
            $jobAd->Category = $categoryObject;
        } else {
            $jobAd->CategoryID = 0;
        }

        // todo countries and sub categories sync

        //
        // $country = $this->findFieldWithName($fields, 'Country');
        // if ($country !== null) {
        //
        //     $countryObject = JobCountry::get()->filter(['JobAdderReference' => $country['valueId']])->first();
        //     if ($countryObject === null) {
        //         $countryObject = JobCountry::create();
        //         $countryObject->Title = $country['value'];
        //         $countryObject->JobAdderReference = $country['valueId'];
        //         $countryObject->write();
        //     }
        //     $jobAd->Country = $countryObject;
        //
        //     $countryFields = $country['fields'];
        //
        //     $location = $this->findFieldWithName($countryFields, 'Location');
        //
        //     if ($location !== null) {
        //         $locationObject = JobLocation::get()->filter(['JobAdderReference' => $location['valueId']])->first();
        //         if ($locationObject === null) {
        //             $locationObject = JobLocation::create();
        //             $locationObject->Title = $location['value'];
        //             $locationObject->JobAdderReference = $location['valueId'];
        //         }
        //         $locationObject->Country = $countryObject;
        //         $locationObject->write();
        //         $jobAd->Location = $locationObject;
        //     }
        //
        // } else {
        //
        //     $location = $this->findFieldWithName($fields, 'Location');
        //
        //     if ($location !== null) {
        //         $locationObject = JobLocation::get()->filter(['JobAdderReference' => $location['valueId']])->first();
        //         if ($locationObject === null) {
        //             $locationObject = JobLocation::create();
        //             $locationObject->Title = $location['value'];
        //             $locationObject->JobAdderReference = $location['valueId'];
        //         }
        //         $locationObject->write();
        //         $jobAd->Location = $locationObject;
        //     }
        //
        // }
        //
        // $category = $this->findFieldWithName($fields, 'Category');
        // if ($category !== null) {
        //
        //     $categoryObject = JobCategory::get()->filter(['JobAdderReference' => $category['valueId']])->first();
        //     if ($categoryObject === null) {
        //         $categoryObject = JobCategory::create();
        //         $categoryObject->Title = $category['value'];
        //         $categoryObject->JobAdderReference = $category['valueId'];
        //         $categoryObject->write();
        //     }
        //     $jobAd->Category = $categoryObject;
        //
        //     $categoryFields = $category['fields'];
        //
        //     $subCategory = $this->findFieldWithName($categoryFields, 'Sub-Category');
        //
        //     if ($subCategory !== null) {
        //         $subCategoryObject = JobSubCategory::get()->filter(['JobAdderReference' => $subCategory['valueId']])->first();
        //         if ($subCategoryObject === null) {
        //             $subCategoryObject = JobSubCategory::create();
        //             $subCategoryObject->Title = $subCategory['value'];
        //             $subCategoryObject->JobAdderReference = $subCategory['valueId'];
        //         }
        //         $subCategoryObject->Category = $categoryObject;
        //         $subCategoryObject->write();
        //         $jobAd->SubCategory = $subCategoryObject;
        //     }
        //
        // }

        $this->extend('updateSyncJobAdFieldsData', $jobAd, $fields);

    }

    /**
     * @throws Exception
     */
    public function cleanupExpiredJobAds()
    {
        $expiredJobAds = JobAd::get()->filter(['ExpiresAt:LessThan' => date('Y-m-d')]);

        foreach ($expiredJobAds as /** @var $expiredJobAd JobAd */ $expiredJobAd) {
            $this->addOutput('Deleted expired Job Ad: ' . $expiredJobAd->Title . ' (' . $expiredJobAd->JobAdderReference . ')');
            $expiredJobAd->delete();
        }
    }

    /**
     * @throws Exception
     */
    public function cleanupDeletedJobAds()
    {
        $expiredJobAds = JobAd::get()->filter(['LastEdited:LessThan' => date('Y-m-d H:i:s', strtotime('-1 hour'))]);

        foreach ($expiredJobAds as /** @var $expiredJobAd JobAd */ $expiredJobAd) {
            $this->addOutput('Deleted out of date Job Ad: ' . $expiredJobAd->Title . ' (' . $expiredJobAd->JobAdderReference . ')');
            $expiredJobAd->delete();
        }
    }

    /**
     * @throws Exception
     */
    public function cleanupUnusedDataobjects()
    {
        $this->cleanupUnusedCountries();
        $this->cleanupUnusedLocations();
        $this->cleanupUnusedCategories();
        $this->cleanupUnusedSubCategories();
        $this->cleanupUnusedSalaryFrequencies();
        $this->cleanupUnusedWorkTypes();
        $this->cleanupUnusedCurrencies();
    }

    /**
     * @throws Exception
     */
    public function cleanupUnusedCountries()
    {
        $sqlQuery = new SQLSelect();
        $sqlQuery->setFrom('JobCountry');
        $sqlQuery->addLeftJoin('JobAd', '"JobCountry"."ID" = "JobAd"."CountryID"');
        $sqlQuery->setSelect('JobCountry.ID', 'JobCountry.Title');
        $sqlQuery->selectField('JobAd.ID', 'JobAdID');
        $sqlQuery->setWhere('"JobAd"."ID" IS NULL');
        $result = $sqlQuery->execute();

        if ($result->numRecords() > 0) {

            foreach ($result as $row) {
                $object = JobCountry::get()->filter(['ID' => $row['ID']])->first();
                $object->delete();
                $this->addOutput('Deleted unused country ' . $row['Title'] . '(' . $row['ID'] . ')');
            }

        }
    }

    /**
     * @throws Exception
     */
    public function cleanupUnusedLocations()
    {
        $sqlQuery = new SQLSelect();
        $sqlQuery->setFrom('JobLocation');
        $sqlQuery->addLeftJoin('JobAd', '"JobLocation"."ID" = "JobAd"."LocationID"');
        $sqlQuery->setSelect('JobLocation.ID', 'JobLocation.Title');
        $sqlQuery->selectField('JobAd.ID', 'JobAdID');
        $sqlQuery->setWhere('"JobAd"."ID" IS NULL');
        $result = $sqlQuery->execute();

        if ($result->numRecords() > 0) {

            foreach ($result as $row) {
                $object = JobLocation::get()->filter(['ID' => $row['ID']])->first();
                $object->delete();
                $this->addOutput('Deleted unused location ' . $row['Title'] . '(' . $row['ID'] . ')');
            }

        }
    }

    /**
     * @throws Exception
     */
    public function cleanupUnusedCategories()
    {
        $sqlQuery = new SQLSelect();
        $sqlQuery->setFrom('JobCategory');
        $sqlQuery->addLeftJoin('JobAd', '"JobCategory"."ID" = "JobAd"."CategoryID"');
        $sqlQuery->setSelect('JobCategory.ID', 'JobCategory.Title');
        $sqlQuery->selectField('JobAd.ID', 'JobAdID');
        $sqlQuery->setWhere('"JobAd"."ID" IS NULL');
        $result = $sqlQuery->execute();

        if ($result->numRecords() > 0) {

            foreach ($result as $row) {
                $object = JobCategory::get()->filter(['ID' => $row['ID']])->first();
                $object->delete();
                $this->addOutput('Deleted unused category ' . $row['Title'] . '(' . $row['ID'] . ')');
            }

        }
    }

    /**
     * @throws Exception
     */
    public function cleanupUnusedSubCategories()
    {
        $sqlQuery = new SQLSelect();
        $sqlQuery->setFrom('JobSubCategory');
        $sqlQuery->addLeftJoin('JobAd', '"JobSubCategory"."ID" = "JobAd"."SubCategoryID"');
        $sqlQuery->setSelect('JobSubCategory.ID', 'JobSubCategory.Title');
        $sqlQuery->selectField('JobAd.ID', 'JobAdID');
        $sqlQuery->setWhere('"JobAd"."ID" IS NULL');
        $result = $sqlQuery->execute();

        if ($result->numRecords() > 0) {

            foreach ($result as $row) {
                $object = JobSubCategory::get()->filter(['ID' => $row['ID']])->first();
                $object->delete();
                $this->addOutput('Deleted unused sub category ' . $row['Title'] . '(' . $row['ID'] . ')');
            }

        }
    }

    /**
     * @throws Exception
     */
    public function cleanupUnusedSalaryFrequencies()
    {
        $sqlQuery = new SQLSelect();
        $sqlQuery->setFrom('JobSalaryFrequency');
        $sqlQuery->addLeftJoin('JobAd', '"JobSalaryFrequency"."ID" = "JobAd"."SalaryFrequencyID"');
        $sqlQuery->setSelect('JobSalaryFrequency.ID', 'JobSalaryFrequency.Title');
        $sqlQuery->selectField('JobAd.ID', 'JobAdID');
        $sqlQuery->setWhere('"JobAd"."ID" IS NULL');
        $result = $sqlQuery->execute();

        if ($result->numRecords() > 0) {

            foreach ($result as $row) {
                $object = JobSalaryFrequency::get()->filter(['ID' => $row['ID']])->first();
                $object->delete();
                $this->addOutput('Deleted unused salary frequency ' . $row['Title'] . '(' . $row['ID'] . ')');
            }

        }
    }

    /**
     * @throws Exception
     */
    public function cleanupUnusedWorkTypes()
    {
        $sqlQuery = new SQLSelect();
        $sqlQuery->setFrom('JobWorkType');
        $sqlQuery->addLeftJoin('JobAd', '"JobWorkType"."ID" = "JobAd"."WorkTypeID"');
        $sqlQuery->setSelect('JobWorkType.ID', 'JobWorkType.Title');
        $sqlQuery->selectField('JobAd.ID', 'JobAdID');
        $sqlQuery->setWhere('"JobAd"."ID" IS NULL');
        $result = $sqlQuery->execute();

        if ($result->numRecords() > 0) {

            foreach ($result as $row) {
                $object = JobWorkType::get()->filter(['ID' => $row['ID']])->first();
                $object->delete();
                $this->addOutput('Deleted unused work type ' . $row['Title'] . '(' . $row['ID'] . ')');
            }

        }
    }

    /**
     * @throws Exception
     */
    public function cleanupUnusedCurrencies()
    {
        $sqlQuery = new SQLSelect();
        $sqlQuery->setFrom('JobCurrency');
        $sqlQuery->addLeftJoin('JobAd', '"JobCurrency"."ID" = "JobAd"."CurrencyID"');
        $sqlQuery->setSelect('JobCurrency.ID', 'JobCurrency.Title');
        $sqlQuery->selectField('JobAd.ID', 'JobAdID');
        $sqlQuery->setWhere('"JobAd"."ID" IS NULL');
        $result = $sqlQuery->execute();

        if ($result->numRecords() > 0) {

            foreach ($result as $row) {
                $object = JobCurrency::get()->filter(['ID' => $row['ID']])->first();
                $object->delete();
                $this->addOutput('Deleted unused currency ' . $row['Title'] . '(' . $row['ID'] . ')');
            }

        }
    }

    /**
     * @param string $output
     * @throws Exception
     */
    public function addOutput(string $output)
    {
        $now = new DateTime();

        $this->output[] = [
            'Message'   => $output,
            'Timestamp' => $now->format('Y-m-d H:i:s'),
        ];

        echo $output . PHP_EOL;
    }

    /**
     * @param array $fields
     * @param string $name
     * @return mixed|null
     */
    public function findFieldWithName(array $fields, string $name)
    {
        foreach ($fields as $field) {
            if ($field['fieldName'] === $name) {
                return $field;
            }
        }

        return null;
    }
}
