<?php

namespace BiffBangPow\JobAdderJobBoard\Admins;

use BiffBangPow\JobAdderJobBoard\DataObjects\JobCategory;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobConsultant;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobCountry;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobCurrency;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobLocation;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobSalaryFrequency;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobSubCategory;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobWorkType;
use SilverStripe\Admin\ModelAdmin;

class JobsTaxonomyAdmin extends ModelAdmin
{
    private static $managed_models = [
        JobCategory::class,
        JobSubCategory::class,
        JobCountry::class,
        JobLocation::class,
        JobCurrency::class,
        JobWorkType::class,
        JobSalaryFrequency::class,
        JobConsultant::class,
    ];

    private static $url_segment = 'jobs-taxonomy';

    private static $menu_title = 'Jobs Taxonomy';
}
