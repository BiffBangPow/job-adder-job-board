<?php

namespace BiffBangPow\JobAdderJobBoard\DataObjects;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBText;
use SilverStripe\ORM\FieldType\DBVarchar;

/**
 * Class JobAdderSyncRecord
 * @package BiffBangPow\JobAdderJobBoard\DataObjects
 *
 * @property string Started
 * @property string Finished
 * @property string Output
 */
class JobAdderSyncRecord extends DataObject
{
    /**
     * @var string
     */
    private static $table_name = 'JobAdderSyncRecord';

    /**
     * @var array
     */
    private static $db = [
        'Started'  => DBVarchar::class,
        'Finished' => DBVarchar::class,
        'Output'   => DBText::class,
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'Started'  => 'Started',
        'Finished' => 'Finished',
    ];

    /**
     * @var string
     */
    private static $default_sort = 'Started';

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('Started');
        $fields->removeByName('Finished');
        $fields->removeByName('Output');

        $fields->addFieldsToTab('Root.Main', [
            TextField::create('Started', 'Started')->setReadonly(true),
            TextField::create('Finished', 'Finished')->setReadonly(true),
            TextareaField::create('Sync output', 'Sync output')->setValue($this->Output)->setRows(30)->setReadonly(true),
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