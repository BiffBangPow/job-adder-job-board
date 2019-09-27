<?php

namespace BiffBangPow\JobAdderJobBoard\Pages;

use Page;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\NumericField;
use SilverStripe\ORM\FieldType\DBInt;

class JobAdderJobBoardPage extends Page
{
    /**
     * @var string
     */
    private static $table_name = 'JobAdderJobBoardPage';

    public static $db = [
        'JobsPerPage' => DBInt::class
    ];

    public static $defaults = [
        'JobsPerPage' => 8
    ];

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldsToTab('Root.Main', [
            NumericField::create('JobsPerPage', 'Jobs Per Page')->setDescription('Set to 0 to disable pagination'),
        ]);
        return $fields;
    }
}
