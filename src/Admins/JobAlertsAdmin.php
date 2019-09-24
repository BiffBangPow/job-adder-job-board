<?php

namespace BiffBangPow\JobAdderJobBoard\Admins;

use BiffBangPow\JobAdderJobBoard\DataObjects\JobAlertSubscription;
use SilverStripe\Admin\ModelAdmin;

class JobAlertsAdmin extends ModelAdmin
{
    private static $managed_models = [
        JobAlertSubscription::class,
    ];

    private static $url_segment = 'job-alerts';

    private static $menu_title = 'Job Alerts';
}