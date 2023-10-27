<?php

namespace BiffBangPow\JobAdderJobBoard\DataObjects;

use BiffBangPow\JobAdderJobBoard\Extensions\JobAdderReferenceExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBVarchar;

/**
 * Class JobConsultant
 * @package BiffBangPow\JobAdderJobBoard\DataObjects
 *
 * @property string FirstName
 * @property string LastName
 * @property string Position
 * @property string JobTitle
 * @property string Email
 * @property string Phone
 * @property string Mobile
 * @property string PhotoURL
 * @property string JobAdderReference
 * @method JobAd[] JobAds
 */
class JobConsultant extends DataObject
{
    /**
     * @var string
     */
    private static $table_name = 'JobConsultant';

    /**
     * @var array
     */
    private static $db = [
        'FirstName'   => DBVarchar::class,
        'LastName'    => DBVarchar::class,
        'LinkedIn'    => DBVarchar::class,
        'Position'    => DBVarchar::class,
        'JobTitle'    => DBVarchar::class,
        'Email'       => DBVarchar::class,
        'Phone'       => DBVarchar::class,
        'Mobile'      => DBVarchar::class,
        'PhotoURL'    => DBVarchar::class,
        'APIPhotoURL' => DBVarchar::class,
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
    private static $summary_fields = [
        'FirstName'    => 'First Name',
        'LastName'     => 'Last Name',
        'LinkedIn'     => 'LinkedIn',
        'JobAds.Count' => 'No Of Job Ads',
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
    private static $default_sort = 'LastName';

    /**
     * @return string
     */
    public function getFullName()
    {
        return sprintf('%s %s', $this->FirstName, $this->LastName);
    }

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldsToTab('Root.Main', [
            TextField::create('FirstName')->setReadOnly(true),
            TextField::create('LastName')->setReadOnly(true),
            TextField::create('LinkedIn'),
            TextField::create('Position')->setReadOnly(true),
            TextField::create('JobTitle')->setReadOnly(true),
            TextField::create('Email')->setReadOnly(true),
            TextField::create('Phone')->setReadOnly(true),
            TextField::create('Mobile')->setReadOnly(true),
            TextField::create('PhotoURL')->setReadOnly(true),
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
}
