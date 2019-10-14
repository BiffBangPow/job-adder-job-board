<?php

namespace BiffBangPow\JobAdderJobBoard\DataObjects;

use BiffBangPow\JobAdderJobBoard\Extensions\JobAdderReferenceExtension;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBVarchar;

/**
 * Class JobCategory
 * @package BiffBangPow\JobAdderJobBoard\DataObjects
 *
 * @property string Title
 * @property string JobAdderReference
 * @method JobSubCategory[] SubCategories
 * @method JobAd[] JobAds
 */
class JobCategory extends DataObject
{
    /**
     * @var string
     */
    private static $table_name = 'JobCategory';

    /**
     * @var array
     */
    private static $db = [
        'Title' => DBVarchar::class,
    ];

    /**
     * @var array
     */
    private static $has_many = [
        'SubCategories' => JobSubCategory::class,
        'JobAds'        => JobAd::class,
    ];

    /**
     * @var array
     */
    private static $cascade_deletes = [
        'SubCategories',
    ];

    /**
     * @var array
     */
    private static $belongs_many_many = [
        'JobAlertSubscriptions' => JobAlertSubscription::class,
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'Title'               => 'Title',
        'SubCategories.Count' => 'No Of SubCategories',
        'JobAds.Count'        => 'No Of Job Ads',
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

        $fields->addFieldsToTab('Root.Main', [
            TextField::create('Title', 'Title')->setReadonly(true),
        ]);
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
        return true;
    }
}
