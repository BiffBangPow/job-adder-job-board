<?php

namespace BiffBangPow\JobAdderJobBoard\DataObjects;

use BiffBangPow\JobAdderJobBoard\Extensions\JobAdderReferenceExtension;
use BiffBangPow\JobAdderJobBoard\Pages\JobAdderJobBoardPage;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\FieldType\DBFloat;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\FieldType\DBText;
use SilverStripe\ORM\FieldType\DBVarchar;

/**
 * Class JobAd
 * @package BiffBangPow\JobAdderJobBoard\DataObjects
 *
 * @property string Title
 * @property string JobAdderId
 * @property string Description
 * @property string Summary
 * @property string BulletPoints
 * @property string JobAdderReference
 * @property DBDatetime PostedAt
 * @property DBDatetime UpdatedAt
 * @property DBDatetime ExpiresAt
 * @property string SalaryRatePer
 * @property DBFloat SalaryRateLow
 * @property DBFloat SalaryRateHigh
 * @property string DisplayLocation
 * @property string DisplaySalary
 * @property boolean HotJob
 * @property string ApplicationLink
 * @method JobCountry Country()
 * @method JobLocation Location()
 * @method JobCategory Category()
 * @method JobSubCategory SubCategory()
 * @method JobCurrency Currency()
 * @method JobWorkType WorkType()
 * @method JobSalaryFrequency SalaryFrequency()
 * @method JobConsultant Consultant()
 */
class JobAd extends DataObject
{
    /**
     * @var string
     */
    private static $table_name = 'JobAd';

    /**
     * @var array
     */
    private static $db = [
        'Title'           => DBVarchar::class,
        'JobAdderId'      => DBVarchar::class,
        'Description'     => 'HTMLText',
        'Summary'         => DBText::class,
        'BulletPoints'    => 'HTMLText',
        'PostedAt'        => DBDatetime::class,
        'UpdatedAt'       => DBDatetime::class,
        'ExpiresAt'       => DBDatetime::class,
        'SalaryRateLow'   => DBFloat::class,
        'SalaryRateHigh'  => DBFloat::class,
        'DisplayLocation' => DBVarchar::class,
        'DisplaySalary'   => DBVarchar::class,
        'HotJob'          => DBBoolean::class,
        'ApplicationLink' => DBVarchar::class,
        'Slug'            => DBVarchar::class,
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'Country'         => JobCountry::class,
        'Location'        => JobLocation::class,
        'Category'        => JobCategory::class,
        'SubCategory'     => JobSubCategory::class,
        'Currency'        => JobCurrency::class,
        'WorkType'        => JobWorkType::class,
        'SalaryFrequency' => JobSalaryFrequency::class,
        'Consultant'      => JobConsultant::class,
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'Title' => 'Title',
    ];

    /**
     * @var array
     */
    private static $extensions = [
        JobAdderReferenceExtension::class,
    ];

    /**
     * @var string
     */
    private static $default_sort = 'Title';

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('CountryID');
        $fields->removeByName('CategoryID');
        $fields->removeByName('LocationID');
        $fields->removeByName('SubCategoryID');
        $fields->removeByName('ConsultantID');

        $fields->addFieldsToTab('Root.Main', [
            DropdownField::create('CountryID', 'Country', JobCountry::get()->map('ID', 'TitleAndReference')->toArray()),
            DropdownField::create('CategoryID', 'Category', JobCategory::get()->map('ID', 'TitleAndReference')->toArray()),
            DropdownField::create('ConsultantID', 'Consultant', JobConsultant::get()->map('ID', 'getFullName')->toArray()),
        ]);

        if ($this->CountryID !== 0) {
            $fields->addFieldsToTab('Root.Main', [
                DropdownField::create('LocationID', 'Location', $this->Country()->Locations()->map('ID', 'TitleAndReference')->toArray()),
            ]);
        }

        if ($this->CategoryID !== 0) {
            $fields->addFieldsToTab('Root.Main', [
                DropdownField::create('SubCategoryID', 'SubCategory', $this->Category()->SubCategories()->map('ID', 'TitleAndReference')->toArray()),
            ]);
        }

        return $fields;
    }

    /**
     * @return string
     */
    public function getLocationString()
    {
        $string = '';

        if ($this->DisplayLocation !== '' && $this->DisplayLocation !== null) {

            return $this->DisplayLocation;

        }

        if ($this->Country() !== null) {

            $string .= $this->Country()->Title;

            if ($this->Location() !== null) {

                $string .= ' - ' . $this->Location()->Title;

            }

        } else {

            if ($this->Location() !== null) {

                $string .= $this->Location()->Title;

            }

        }

        return $string;
    }

    /**
     * @return string
     */
    public function getCategoryString()
    {
        $string = '';

        if ($this->Category() !== null) {

            $string .= $this->Category()->Title;

            if ($this->SubCategory() !== null) {

                $string .= ' - ' . $this->SubCategory()->Title;

            }

        }

        return $string;
    }

    /**
     * @return mixed
     */
    public function getSummaryOrExcerpt()
    {
        if ($this->Summary !== null && $this->Summary !== '') {

            return $this->owner->Summary;

        } else {

            return $this->Excerpt(100);

        }
    }

    /**
     * @param int $wordsToDisplay
     *
     * @return string
     */
    public function Excerpt($wordsToDisplay = 100)
    {
        /** @var DBHTMLText $content */
        $description = $this->dbObject('Description');
        return $description->Summary($wordsToDisplay);
    }

    /**
     * @return string
     */
    public function Link()
    {
        $jobBoardPage = JobAdderJobBoardPage::get()->first();
        return $jobBoardPage->Link('job/' . $this->Slug);
    }

    /**
     * @return string
     */
    public function AbsoluteLink()
    {
        $jobBoardPage = JobAdderJobBoardPage::get()->first();
        return $jobBoardPage->AbsoluteLink('job/' . $this->Slug);
    }

    /**
     * @return mixed
     */
    public function getApplyLink()
    {
        $jobBoardPage = JobAdderJobBoardPage::get()->first();
        return $jobBoardPage->Link('apply/' . $this->Slug);
    }

    /**
     * @param null $member
     * @param array $context
     * @return bool
     */
    public function canDelete($member = null, $context = [])
    {
        return false;
    }

    /**
     * @param null $member
     * @param array $context
     * @return bool
     */
    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    /**
     * @param null $member
     * @param array $context
     * @return bool
     */
    public function canView($member = null, $context = [])
    {
        return true;
    }

    /**
     * @param null $member
     * @param array $context
     * @return bool
     */
    public function canEdit($member = null, $context = [])
    {
        return false;
    }
}
