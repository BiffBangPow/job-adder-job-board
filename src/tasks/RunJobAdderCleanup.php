<?php

use BiffBangPow\JobAdderJobBoard\DataObjects\JobAd;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobCategory;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobCountry;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobCurrency;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobLocation;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobSalaryFrequency;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobSubCategory;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobWorkType;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\Queries\SQLSelect;

class RunJobAdderCleanup extends BuildTask
{
    protected $title = 'Run Job Adder Cleanup';

    protected $description = 'Cleans up old jobs and data objects';

    protected $enabled = true;

    private $output = [];

    /**
     * @param HTTPRequest $request
     * @throws Exception
     */
    public function run($request)
    {
        $this->cleanup();
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
     * @throws Exception
     */
    private function cleanupExpiredJobAds()
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
    private function cleanupDeletedJobAds()
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
    private function cleanupUnusedDataobjects()
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
    private function cleanupUnusedCountries()
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
    private function cleanupUnusedLocations()
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
    private function cleanupUnusedCategories()
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
    private function cleanupUnusedSubCategories()
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
    private function cleanupUnusedSalaryFrequencies()
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
    private function cleanupUnusedWorkTypes()
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
    private function cleanupUnusedCurrencies()
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
    private function addOutput(string $output)
    {
        $now = new DateTime();

        $this->output[] = [
            'Message'   => $output,
            'Timestamp' => $now->format('Y-m-d H:i:s'),
        ];

        echo $output . PHP_EOL;
    }
}
