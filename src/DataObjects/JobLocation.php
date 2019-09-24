<?php

namespace BiffBangPow\JobAdderJobBoard\DataObjects;

use BiffBangPow\JobAdderJobBoard\Extensions\JobAdderReferenceExtension;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBVarchar;

/**
 * Class JobLocation
 * @package BiffBangPow\JobAdderJobBoard\DataObjects
 *
 * @property string Title
 * @property string JobAdderReference
 */
class JobLocation extends DataObject
{
    /**
     * @var string
     */
    private static $table_name = 'JobLocation';

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
        'Country' => JobCountry::class,
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
        'Title'         => 'Title',
        'Country.Title' => 'Country',
        'JobAds.Count'  => 'No Of Job Ads',
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

        $fields->removeByName('JobAlertSubscriptions');
        $fields->removeByName('JobAds');

        $fields->addFieldsToTab('Root.Main', [
            TextField::create('Title', 'Title')->setReadonly(true),
            DropdownField::create('CountryID', 'Country', JobCountry::get()->map()->toArray())->setReadonly(true),
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
        return true;
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
