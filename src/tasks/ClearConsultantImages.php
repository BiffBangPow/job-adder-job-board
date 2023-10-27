<?php

use BiffBangPow\JobAdderJobBoard\DataObjects\JobAd;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobAdderSyncRecord;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobCategory;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobConsultant;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobCountry;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobCurrency;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobLocation;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobSalaryFrequency;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobSubCategory;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobWorkType;
use GuzzleHttp\Exception\GuzzleException;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\Queries\SQLDelete;
use SilverStripe\ORM\Queries\SQLSelect;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\SiteConfig\SiteConfig;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Injector\Injector;

class ClearConsultantImages extends BuildTask
{
    protected $title = 'Clear consultant images';

    protected $description = 'Clears consultant images';

    protected $enabled = true;

    /**
     * @param HTTPRequest $request
     * @throws GuzzleException
     * @throws ValidationException
     * @throws Exception
     */
    public function run($request)
    {
        $consultants = JobConsultant::get();

        foreach ($consultants as $consultant) {
            echo sprintf('Removing image for %s %s', $consultant->FirstName, $consultant->LastName) . PHP_EOL;
            $consultant->PhotoURL = '';
            $consultant->write();
        }
    }
}
