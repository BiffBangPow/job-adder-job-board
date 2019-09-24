<?php

namespace BiffBangPow\JobAdderJobBoard\DataObjects;

use BiffBangPow\JobAdderJobBoard\Extensions\JobAdderReferenceExtension;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBVarchar;

/**
 * Class JobSubCategory
 * @package BiffBangPow\JobAdderJobBoard\DataObjects
 *
 * @property string Title
 * @property string JobAdderReference
 */
class JobSubCategory extends DataObject
{
    /**
     * @var string
     */
    private static $table_name = 'JobSubCategory';

    /**
     * @var array
     */
    private static $db = [
        'Title' => DBVarchar::class,
    ];

    /**
     * @var array
     */
    private static $has_one = [
        'Category' => JobCategory::class,
    ];

    /**
     * @var array
     */
    private static $has_many = [
        'JobAds' => JobAd::class,
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
        'Title'          => 'Title',
        'Category.Title' => 'Category',
        'JobAds.Count'   => 'No Of Job Ads',
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
            DropdownField::create('CategoryID', 'Category', JobCategory::get()->map()->toArray()),
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
        return false;
    }
}