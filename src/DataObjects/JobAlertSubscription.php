<?php

namespace BiffBangPow\JobAdderJobBoard\DataObjects;

use BiffBangPow\JobAdderJobBoard\Controllers\JobAlertsController;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\FieldType\DBVarchar;

/**
 * Class JobAlertSubscription
 * @package BiffBangPow\JobAdderJobBoard\DataObjects
 *
 * @property string Name
 * @property string EmailAddress
 * @property string Hash
 * @property DBDatetime CreatedDate
 * @property DBDatetime AlertsLastSent
 * @method JobCountry[] Categories()
 * @method JobLocation[] SubCategories()
 * @method JobCategory[] Countries()
 * @method JobSubCategory[] Locations()
 * @method JobCurrency[] WorkTypes()
 */
class JobAlertSubscription extends DataObject
{
    /**
     * @var string
     */
    private static $table_name = 'JobAlertSubscription';

    /**
     * @var array
     */
    private static $db = [
        'Name'           => DBVarchar::class,
        'EmailAddress'   => DBVarchar::class,
        'Hash'           => DBVarchar::class,
        'CreatedDate'    => DBDatetime::class,
        'AlertsLastSent' => DBDatetime::class,
    ];

    /**
     * @var array
     */
    private static $many_many = [
        'Categories'    => JobCategory::class,
        'SubCategories' => JobSubCategory::class,
        'Countries'     => JobCountry::class,
        'Locations'     => JobLocation::class,
        'WorkTypes'     => JobWorkType::class,
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'Name'                => 'Name',
        'EmailAddress'        => 'EmailAddress',
        'SubCategories.Count' => 'No Of SubCategories',
        'Categories.Count'    => 'No Of Categories',
        'Countries.Count'     => 'No Of Countries',
        'Locations.Count'     => 'No Of Locations',
        'WorkTypes.Count'     => 'No Of Work Types',
    ];

    /**
     * @return string
     */
    public function getUnsubscribeLink()
    {
        $controller = new JobAlertsController();
        return $controller->Link('unsubscribe/' . $this->Hash);
    }

    /**
     * @return string
     */
    public function getUpdateSubscriptionLink()
    {
        $controller = new JobAlertsController();
        return $controller->Link('updatesubscription/' . $this->Hash);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        $description = '';

        if ($this->Countries()->count() > 0) {

            $description .= '<p>Countries</p><ul>';

            foreach ($this->Countries() as $country) {

                $description .= '<li>' . $country->Title . '</li>';

            }

            $description .= '</ul>';

        }

        if ($this->Locations()->count() > 0) {

            $description .= '<p>Locations</p><ul>';

            foreach ($this->Locations() as $location) {

                $description .= '<li>' . $location->Title . '</li>';

            }

            $description .= '</ul>';

        }

        if ($this->Categories()->count() > 0) {

            $description .= '<p>Categories</p><ul>';

            foreach ($this->Categories() as $category) {

                $description .= '<li>' . $category->Title . '</li>';

            }

            $description .= '</ul>';

        }

        if ($this->SubCategories()->count() > 0) {

            $description .= '<p>Sub categories</p><ul>';

            foreach ($this->SubCategories() as $subCategory) {

                $description .= '<li>' . $subCategory->Title . '</li>';

            }

            $description .= '</ul>';

        }

        if ($this->WorkTypes()->count() > 0) {

            $description .= '<p>Work types</p><ul>';

            foreach ($this->WorkTypes() as $workType) {

                $description .= '<li>' . $workType->Title . '</li>';

            }

            $description .= '</ul>';

        }

        return $description;

    }

    /**
     * @var string
     */
    private static $default_sort = 'CreatedDate DESC';

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        return $fields;
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