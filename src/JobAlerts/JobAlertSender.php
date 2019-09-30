<?php

use BiffBangPow\JobAdderJobBoard\DataObjects\JobAd;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobAlertSubscription;
use SilverStripe\Control\Email\Email;
use SilverStripe\SiteConfig\SiteConfig;

class JobAlertSender
{
    public function sendJobAlerts()
    {
        foreach ($this->getJobAlertSubscriptions() as $jobAlertSubscription) {

            $jobs = JobAd::get()->filter([
                'PostedAt:GreaterThan' => $jobAlertSubscription->AlertsLastSent
            ]);

            if ($jobAlertSubscription->Countries()->count() > 0) {

                $countryIds = $jobAlertSubscription->Countries()->column('ID');

                $jobs = $jobs->filter(
                    [
                        'Country.ID' => $countryIds,
                    ]
                );

            }

            if ($jobAlertSubscription->Locations()->count() > 0) {

                $locationIds = $jobAlertSubscription->Locations()->column('ID');

                $jobs = $jobs->filter(
                    [
                        'Location.ID' => $locationIds,
                    ]
                );

            }

            if ($jobAlertSubscription->Categories()->count() > 0) {

                $categoryIds = $jobAlertSubscription->Categories()->column('ID');

                $jobs = $jobs->filter(
                    [
                        'Category.ID' => $categoryIds,
                    ]
                );

            }

            if ($jobAlertSubscription->SubCategories()->count() > 0) {

                $subCategoryIds = $jobAlertSubscription->SubCategories()->column('ID');

                $jobs = $jobs->filter(
                    [
                        'SubCategory.ID' => $subCategoryIds,
                    ]
                );

            }

            if ($jobAlertSubscription->WorkTypes()->count() > 0) {

                $workTypeIds = $jobAlertSubscription->WorkTypes()->column('ID');

                $jobs = $jobs->filter(
                    [
                        'WorkType.ID' => $workTypeIds,
                    ]
                );

            }

            $this->sendJobAlertEmail($jobAlertSubscription, $jobs);
        }

    }

    private function sendJobAlertEmail($jobAlertSubscription, $jobs)
    {
        if ($jobs->count() > 0) {

            echo 'Sending job alert to ' . $jobAlertSubscription->EmailAddress . ' with ' . $jobs->count() . ' jobs' . PHP_EOL;

            $siteConfig = SiteConfig::current_site_config();
            $email = Email::create();
            $email->setHTMLTemplate('Email\\JobAlertsEmail');
            $email->setFrom($siteConfig->JobAlertsEmailsFrom, $siteConfig->JobAlertsBrandName);
            $email->setTo($jobAlertSubscription->EmailAddress);
            $email->setSubject('New jobs from ' . $siteConfig->JobAlertsBrandName);
            $email->setData([
                'JobAlertSubscription' => $jobAlertSubscription,
                'BrandName'            => $siteConfig->JobAlertsBrandName,
                'Jobs'                 => $jobs,
            ]);
            $email->send();

        } else {

            echo 'No jobs to send to ' . $jobAlertSubscription->EmailAddress . ' with ' . $jobs->count() . ' jobs' . PHP_EOL;

        }
    }

    /**
     * @return JobAlertSubscription[]
     */
    public function getJobAlertSubscriptions()
    {
        return JobAlertSubscription::get();

    }
}
