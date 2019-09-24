<?php

use GuzzleHttp\Exception\GuzzleException;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Control\HTTPRequest;

class SendJobAlerts extends BuildTask
{
    protected $title = 'Send Job Alerts';

    protected $description = 'Send job alerts to subscribers';

    protected $enabled = true;

    /**
     * @var JobAlertSender
     */
    private $jobAlertSender;

    /**
     * RunJobAdderSync constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->jobAlertSender = new JobAlertSender();
    }


    /**
     * @param HTTPRequest $request
     * @throws GuzzleException
     * @throws ValidationException
     */
    public function run($request)
    {
        $this->jobAlertSender->sendJobAlerts();
    }
}
