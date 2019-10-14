<?php

namespace BiffBangPow\JobAdderJobBoard\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBVarchar;

class JobAdderReferenceExtension extends DataExtension {

    /**
     * @var array
     */
    private static $db = [
        'JobAdderReference' => DBVarchar::class,
    ];

    /**
     * @var array
     */
    private static $summary_fields = [
        'JobAdderReference' => 'Job Adder Reference'
    ];

    /**
     * @param FieldList $fields
     * @return FieldList
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab(
            'Root.Main',
            [
                TextField::create('JobAdderReference', 'JobAdder Reference')->setReadonly(true)
            ]
        );
        return $fields;
    }

    /**
     * @return string
     */
    public function getTitleAndReference()
    {
        return sprintf('%s (%s)', $this->owner->Title, $this->owner->JobAdderReference);
    }
}
