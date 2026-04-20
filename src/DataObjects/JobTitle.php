<?php

namespace BiffBangPow\JobAdderJobBoard\DataObjects;

use SilverStripe\ORM\DataObject;

class JobTitle extends DataObject
{
    private static $table_name = 'JobTitle';
    private static $db = [
        'Title' => 'Varchar(255)'
    ];
    private static $default_sort = 'Title';
    private static $indexes = [
        'Title' => true
    ];
    private static $has_many = [
        'JobAds' => JobAd::class
    ];
    public static function findOrMake($title)
    {
        $bands = self::get()->filter(['Title' => $title]);
        if ($bands->count() > 0) {
            return $bands->first();
        }
        $band = self::create([
            'Title' => $title
        ]);
        $band->write();
        return $band;
    }
}
