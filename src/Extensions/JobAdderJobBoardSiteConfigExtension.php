<?php

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;

class JobAdderJobBoardSiteConfigExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $db = [
        'JobAdderJobBoardState'        => 'Varchar(200)',
        'JobAdderJobBoardAccessToken'  => 'Varchar(200)',
        'JobAdderJobBoardRefreshToken' => 'Varchar(200)',
        'JobAdderJobBoardAPIBaseURL'   => 'Varchar(200)',
        'JobAdderJobBoardJobBoardID'   => 'Varchar(200)',
        'JobAlertsBrandName'           => 'Varchar(200)',
        'JobAlertsEmailsFrom'          => 'Varchar(200)',
        'JobAlertsConsentCheckboxText' => 'HTMLText'
    ];

    /**
     * @param FieldList $fields
     * @return void
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName('JobAdderJobBoardState');

        if (
            $this->owner->JobAdderJobBoardAccessToken !== null &&
            $this->owner->JobAdderJobBoardAccessToken !== '' &&
            $this->owner->JobAdderJobBoardJobBoardID !== null &&
            $this->owner->JobAdderJobBoardJobBoardID !== ''
        ) {
            $authoriseJobAdderText = 'Reauthorise job adder';
            $authorisedState = 'Authorised';
        } else {
            $authoriseJobAdderText = 'Authorise job adder';
            $authorisedState = 'Not authorised';
        }

        $fields->addFieldsToTab('Root.JobAdderJobBoard', [
            TextField::create('AuthorisedState', 'Authorised State')->setValue($authorisedState)->setReadonly(true),
            TextField::create('JobAdderJobBoardState')->setReadonly(true),
            TextField::create('JobAdderJobBoardAccessToken')->setReadonly(true),
            TextField::create('JobAdderJobBoardRefreshToken')->setReadonly(true),
            TextField::create('JobAdderJobBoardAPIBaseURL')->setReadonly(true),
            TextField::create('JobAdderJobBoardJobBoardID')->setReadonly(true),
            TextField::create('JobAdderJobBoardJobBoardID')->setReadonly(true),
            LiteralField::create('AuthoriseLink', 'Authorise Link')->setValue('<a href="/authorise-job-adder" target="_blank">' . $authoriseJobAdderText . '</a>'),
        ]);

        $fields->addFieldsToTab('Root.JobAlerts', [
            TextField::create('JobAlertsBrandName', 'Job Alerts Brand Name'),
            TextField::create('JobAlertsEmailsFrom', 'Job Alerts Emails From'),
            HTMLEditorField::create('JobAlertsConsentCheckboxText', 'Job Alerts Consent Checkbox Text')
        ]);
    }
}
