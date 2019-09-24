<?php

namespace BiffBangPow\JobAdderJobBoard\Admins;

use BiffBangPow\JobAdderJobBoard\DataObjects\JobAd;

use BiffBangPow\JobAdderJobBoard\DataObjects\JobAdderSyncRecord;
use SilverStripe\Admin\ModelAdmin;

class JobsAdmin extends ModelAdmin
{
    private static $managed_models = [
        JobAd::class,
        JobAdderSyncRecord::class,
    ];

    private static $url_segment = 'jobs';

    private static $menu_title = 'Jobs';
}