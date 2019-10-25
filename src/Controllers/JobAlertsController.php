<?php

namespace BiffBangPow\JobAdderJobBoard\Controllers;

use BiffBangPow\JobAdderJobBoard\DataObjects\JobAlertSubscription;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobCategory;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobCountry;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobLocation;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobSubCategory;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobWorkType;
use PageController;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Email\Email;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CheckboxSetField;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ValidationException;
use SilverStripe\SiteConfig\SiteConfig;

class JobAlertsController extends PageController
{
    private static $allowed_actions = [
        'subscribe',
        'SubscribeForm',
        'subscribed',
        'updatesubscription',
        'UpdateSubscriptionForm',
        'unsubscribe',
    ];

    /**
     * @param HTTPRequest $request
     * @return array
     */
    public function subscribe(HTTPRequest $request)
    {
        return [];
    }

    /**
     * @return Form
     * @throws \Exception
     */
    public function SubscribeForm()
    {
        $form = $this->makeJobAlertSubscriptionForm('doCreateSubscription', __FUNCTION__);
        return $form;
    }

    /**
     * @param $data
     * @param Form $form
     * @return HTTPResponse
     * @throws ValidationException
     */
    public function doCreateSubscription($data, $form)
    {
        if ($data['ContactConsent'] === '1' || $data['ContactConsent'] === 1) {

            $jobAlertSubscription = JobAlertSubscription::create();
            $form->saveInto($jobAlertSubscription);
            $jobAlertSubscription->write();

            $siteConfig = SiteConfig::current_site_config();

            $email = Email::create();
            $email->setHTMLTemplate('Email\\JobAlertsSubscribedEmail');
            $email->setFrom($siteConfig->JobAlertsEmailsFrom, $siteConfig->JobAlertsBrandName);
            $email->setTo($jobAlertSubscription->EmailAddress);
            $email->setSubject('Subscribed to job alerts from ' . $siteConfig->JobAlertsBrandName);
            $email->setData([
                'JobAlertSubscription' => $jobAlertSubscription,
                'BrandName'            => $siteConfig->JobAlertsBrandName,
            ]);
            $email->send();

            return $this->redirect($this->Link('subscribed'));

        } else {

            $form->addErrorMessage('ContactConsent', 'We cannot process your data without your consent', 'bad');

            return $this->redirectBack();

        }
    }

    /**
     * @param HTTPRequest $request
     * @return array
     */
    public function subscribed(HTTPRequest $request)
    {
        return [
            'Message' => 'You are now subscribed to job alerts',
        ];
    }

    /**
     * @param HTTPRequest $request
     * @return Form
     * @throws \Exception
     */
    public function UpdateSubscriptionForm(HTTPRequest $request)
    {
        $form = $this->makeJobAlertSubscriptionForm('doUpdateSubscription', __FUNCTION__);

        $hash = $request->param('ID');
        $jobAlertSubscription = JobAlertSubscription::get()->filter(['Hash' => $hash])->first();

        if ($hash !== null && $jobAlertSubscription !== null) {
            $form->loadDataFrom($jobAlertSubscription);
        }

        return $form;
    }

    /**
     * @param HTTPRequest $request
     * @return array
     * @throws \Exception
     */
    public function updatesubscription(HTTPRequest $request)
    {
        $hash = $request->param('ID');
        $updated = $request->getVar('updated');
        $jobAlertSubscription = JobAlertSubscription::get()->filter(['Hash' => $hash])->first();

        if ($hash !== null && $jobAlertSubscription !== null) {

            $data = [
                'UpdateSubscriptionForm' => $this->UpdateSubscriptionForm($request),
            ];

            if ($updated === '1' || $updated === '1') {
                $data['Message'] = 'Your job alerts subscription has been updated';
            }

            return $data;

        }

        return $this->httpError(404, 'Job alert subscription not found');
    }

    /**
     * @param $data
     * @param Form $form
     * @return HTTPResponse
     * @throws ValidationException
     */
    public function doUpdateSubscription($data, $form)
    {
        if ($data['ContactConsent'] === '1' || $data['ContactConsent'] === 1) {

            $hash = $data['Hash'];
            $jobAlertSubscription = JobAlertSubscription::get()->filter(['Hash' => $hash])->first();

            if ($hash !== null && $jobAlertSubscription !== null) {

                $form->saveInto($jobAlertSubscription);
                $jobAlertSubscription->write();

                return $this->redirect($this->Link('updatesubscription/' . $hash, ['updated' => 1]));

            }

            return $this->httpError(404, 'Job alert subscription not found');

        } else {

            $form->addErrorMessage('ContactConsent', 'We cannot process your data without your consent', 'bad');

            return $this->redirectBack();

        }
    }

    /**
     * @param HTTPRequest $request
     * @return array
     */
    public function unsubscribe(HTTPRequest $request)
    {
        $hash = $request->param('ID');

        $jobAlertSubscription = JobAlertSubscription::get()->filter(['Hash' => $hash])->first();

        if ($hash !== null && $jobAlertSubscription !== null) {

            $jobAlertSubscription->delete();
            return [
                'Message' => 'You are now unsubscribed from job alerts',
            ];

        }

        return $this->httpError(404, 'Job alert subscription not found');
    }

    /**
     * @param null $action
     * @param array $params
     * @return string
     */
    public function Link($action = null, $params = [])
    {
        $link = Controller::join_links('job-alerts', $action);

        if (count($params) > 0) {
            $link .= '?' . http_build_query($params);
        }

        $this->extend('updateLink', $link, $action);

        return $link;
    }

    /**
     * @param string $formAction
     * @param string $formFunction
     * @return Form
     * @throws \Exception
     */
    private function makeJobAlertSubscriptionForm(string $formAction, string $formFunction)
    {
        $now = new \DateTime();
        $nowString = $now->format(\DateTime::ISO8601);

        $siteConfig = SiteConfig::current_site_config();
        $checkboxText = $siteConfig->JobAlertsConsentCheckboxText;

        $fields = FieldList::create([
            TextField::create('Name', 'Name')->addExtraClass('col-12'),
            EmailField::create('EmailAddress', 'Email Address')->addExtraClass('col-12'),
            HiddenField::create('Hash', 'Hash')->setValue(uniqid())->addExtraClass('col-12'),
            HiddenField::create('CreatedDate', 'Created Date')->setValue($nowString)->addExtraClass('col-12'),
            HiddenField::create('AlertsLastSent', 'Alerts Last Sent')->setValue($nowString)->addExtraClass('col-12'),
            CheckboxSetField::create('Categories', 'Categories', JobCategory::get()->map()->toArray())->addExtraClass('col-12'),
            CheckboxSetField::create('SubCategories', 'Sub Categories', JobSubCategory::get()->map()->toArray())->addExtraClass('col-12'),
            CheckboxSetField::create('Countries', 'Countries', JobCountry::get()->map()->toArray())->addExtraClass('col-12'),
            CheckboxSetField::create('Locations', 'Locations', JobLocation::get()->map()->toArray())->addExtraClass('col-12'),
            CheckboxSetField::create('WorkTypes', 'Work Types', JobWorkType::get()->map()->toArray())->addExtraClass('col-12'),
            CheckboxField::create('ContactConsent', $checkboxText)->addExtraClass('col-12')
        ]);

        $actions = FieldList::create(
            FormAction::create($formAction, 'Subscribe')
        );

        $form = Form::create(
            $this,
            $formFunction,
            $fields,
            $actions,
            new RequiredFields([
                    'Name',
                    'EmailAddress',
                    'Hash',
                    'CreatedDate',
                    'AlertsLastSent',
                    'ContactConsent'
                ]
            )
        );

        $form->setTemplate('JobAlertsForm');

        return $form;
    }
}
