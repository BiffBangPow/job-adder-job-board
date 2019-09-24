<?php

namespace BiffBangPow\JobAdderJobBoard\Pages;

use BiffBangPow\JobAdderJobBoard\DataObjects\JobAd;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobCategory;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobCountry;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobCurrency;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobLocation;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobSalaryFrequency;
use BiffBangPow\JobAdderJobBoard\DataObjects\JobWorkType;
use PageController;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\PaginatedList;

class JobAdderJobBoardPageController extends PageController
{
    const ITEMS_PER_PAGE = 4;

    const SEARCH = 's';

    const COUNTRY = 'co';

    const LOCATION = 'lo';

    const CATEGORY = 'ca';

    const SUB_CATEGORY = 'sca';

    const CURRENCY = 'cu';

    const SALARY_MIN = 'smin';

    const SALARY_MAX = 'smax';

    const SALARY_PER = 'sper';

    const WORK_TYPE = 'wt';

    public $filters = [];

    /**
     * @var array
     */
    private static $allowed_actions = [
        'job',
        'apply',
    ];

    /**
     * @return PaginatedList
     * @throws \Exception
     */
    public function Results()
    {
        $dataList = JobAd::get();
        $this->filters = [
            'ExpiresAt:GreaterThanOrEqual' => date('Y-m-d'),
        ];

        /** @var HTTPRequest $request */
        $request = $this->getRequest();

        $search = $request->getVar(self::SEARCH);
        if ($this->isParameterSet($search)) {
            $this->filters['Title:PartialMatch'] = $search;
        }

        $countryID = $request->getVar(self::COUNTRY);
        if ($this->isParameterSet($countryID)) {
            $this->filters['Country.ID'] = $countryID;
        }

        $locationID = $request->getVar(self::LOCATION);
        if ($this->isParameterSet($locationID)) {
            $this->filters['Location.ID'] = $locationID;
        }

        $categoryID = $request->getVar(self::CATEGORY);
        if ($this->isParameterSet($categoryID)) {
            $this->filters['Category.ID'] = $categoryID;
        }

        $subCategoryID = $request->getVar(self::SUB_CATEGORY);
        if ($this->isParameterSet($subCategoryID)) {
            $this->filters['SubCategory.ID'] = $subCategoryID;
        }

        $salaryMin = $request->getVar(self::SALARY_MIN);

        if ($this->isParameterSet($salaryMin)) {
            $this->filters['SalaryRateLow:GreaterThanOrEqual'] = $salaryMin;
        }

        $salaryMax = $request->getVar(self::SALARY_MAX);
        if ($this->isParameterSet($salaryMax)) {
            $this->filters['SalaryRateHigh:LessThanOrEqual'] = $salaryMax;
        }

        $currencyID = $request->getVar(self::CURRENCY);
        if ($this->isParameterSet($currencyID)) {
            $this->filters['Currency.ID'] = $currencyID;
        }

        $salaryRatePer = $request->getVar(self::SALARY_PER);
        if ($this->isParameterSet($salaryRatePer)) {
            $this->filters['SalaryFrequency.ID'] = $salaryRatePer;
        }

        $workTypeID = $request->getVar(self::WORK_TYPE);
        if ($this->isParameterSet($workTypeID)) {
            $this->filters['WorkType.ID'] = $workTypeID;
        }

        $this->extend('updateResults', $request, $this->filters);

        $dataList = $dataList->filter($this->filters);
        $dataList = $dataList->sort('PostedAt', 'DESC');

        $paginatedList = new PaginatedList($dataList, $request);
        $paginatedList->setPageLength(self::ITEMS_PER_PAGE);

        return $paginatedList;
    }

    /**
     * @param HTTPRequest $request
     * @return array
     * @throws \Exception
     */
    public function index(HTTPRequest $request)
    {
        return [
            'Results' => $this->Results(),
        ];
    }

    /**
     * @param HTTPRequest $request
     * @return array
     * @throws \Exception
     */
    public function job(HTTPRequest $request)
    {
        $jobAd = JobAd::get()->filter(['Slug' => $request->param('ID')])->first();

        if(!$jobAd) {
            return $this->httpError(404,'That job ad could not be found');
        }

        return [
            'JobAd' => $jobAd
        ];
    }

    /**
     * @param HTTPRequest $request
     * @return array
     * @throws \Exception
     */
    public function apply(HTTPRequest $request)
    {
        $jobAd = JobAd::get()->filter(['Slug' => $request->param('ID')])->first();

        if(!$jobAd) {
            return $this->httpError(404,'That job ad could not be found');
        }

        return [
            'JobAd' => $jobAd
        ];
    }

    /**
     * @return bool
     */
    public function getSearch()
    {
        $search = $this->getRequest()->getVar(self::SEARCH);
        return $search;
    }

    /**
     * @return DataList
     */
    public function getCountries()
    {
        return JobCountry::get();
    }

    /**
     * @return string
     */
    public function getCountryParam()
    {
        return self::COUNTRY;
    }

    /**
     * @param int $countryId
     * @return bool
     */
    public function IsSelectedCountry(int $countryId)
    {
        $countryIds = $this->getRequest()->getVar(self::COUNTRY);

        if (!is_null($countryIds)) {
            if (is_array($countryIds)) {
                return in_array($countryId, $countryIds);
            } else {
                return ((int)$countryIds === (int)$countryId);
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function IsSelectedCountries()
    {
        $countryIds = $this->getRequest()->getVar(self::COUNTRY);
        return (!is_null($countryIds));
    }

    /**
     * @return string
     */
    public function getLocationParam()
    {
        return self::LOCATION;
    }

    /**
     * @return DataList
     */
    public function getLocations()
    {
        return JobLocation::get();
    }

    /**
     * @param int $locationId
     * @return bool
     */
    public function IsSelectedLocation(int $locationId)
    {
        $locationIds = $this->getRequest()->getVar(self::LOCATION);

        if (!is_null($locationIds)) {
            if (is_array($locationIds)) {
                return in_array($locationId, $locationIds);
            } else {
                return ((int)$locationIds === (int)$locationId);
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function IsSelectedLocations()
    {
        $locationIds = $this->getRequest()->getVar(self::LOCATION);
        return (!is_null($locationIds));
    }

    /**
     * @param $countryId
     * @return bool
     */
    public function IsSelectedLocationsInCountry($countryId)
    {
        $locationsIds = $this->getRequest()->getVar(self::LOCATION);

        if (is_array($locationsIds)) {
            /** @var JobCountry $country */
            $country = JobCountry::get()->filter(['ID' => $countryId])->first();
            $locationIdsFromCountry = $country->Locations()->map()->toArray();
            $commonIds = array_intersect(array_keys($locationIdsFromCountry), $locationsIds);
            return (count($commonIds) > 0);
        }

        return false;
    }

    /**
     * @return DataList
     */
    public function getCategories()
    {
        return JobCategory::get();
    }

    /**
     * @return string
     */
    public function getCategoryParam()
    {
        return self::CATEGORY;
    }

    /**
     * @param int $categoryId
     * @return bool
     */
    public function IsSelectedCategory(int $categoryId)
    {
        $categoryIds = $this->getRequest()->getVar(self::CATEGORY);

        if (!is_null($categoryIds)) {
            if (is_array($categoryIds)) {
                return in_array($categoryId, $categoryIds);
            } else {
                return ((int)$categoryIds === (int)$categoryId);
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function IsSelectedCategories()
    {
        $categoryIds = $this->getRequest()->getVar(self::CATEGORY);
        return (!is_null($categoryIds));
    }

    /**
     * @return string
     */
    public function getSubCategoryParam()
    {
        return self::SUB_CATEGORY;
    }

    /**
     * @param int $subCategoryId
     * @return bool
     */
    public function IsSelectedSubCategory(int $subCategoryId)
    {
        $subCategoryIds = $this->getRequest()->getVar(self::SUB_CATEGORY);

        if (!is_null($subCategoryIds)) {
            if (is_array($subCategoryIds)) {
                return in_array($subCategoryId, $subCategoryIds);
            } else {
                return ((int)$subCategoryIds === (int)$subCategoryId);
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function IsSelectedSubCategories()
    {
        $subCategoryIds = $this->getRequest()->getVar(self::SUB_CATEGORY);
        return (!is_null($subCategoryIds));
    }

    /**
     * @param $categoryId
     * @return bool
     */
    public function IsSelectedSubCategoriesInCategory($categoryId)
    {

        $subCategoryIds = $this->getRequest()->getVar(self::SUB_CATEGORY);
        if (is_array($subCategoryIds)) {
            /** @var JobCategory $category */
            $category = JobCategory::get()->filter(['ID' => $categoryId])->first();
            $subCategoryIdsFromCategory = $category->SubCategories()->map()->toArray();
            $commonIds = array_intersect(array_keys($subCategoryIdsFromCategory), $subCategoryIds);
            return (count($commonIds) > 0);
        }

        return false;
    }

    /**
     * @return DataList
     */
    public function getWorkTypes()
    {
        return JobWorkType::get();
    }

    /**
     * @return string
     */
    public function getWorkTypeParam()
    {
        return self::WORK_TYPE;
    }

    /**
     * @param int $workType
     * @return bool
     */
    public function IsSelectedWorkType(int $workType)
    {
        $workTypeIds = $this->getRequest()->getVar(self::WORK_TYPE);
        if (!is_null($workTypeIds)) {
            if (is_array($workTypeIds)) {
                return in_array($workType, $workTypeIds);
            } else {
                return ((int)$workTypeIds === (int)$workType);
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function IsSelectedWorkTypes()
    {
        $workTypeIds = $this->getRequest()->getVar(self::WORK_TYPE);
        return (!is_null($workTypeIds));
    }

    /**
     * @return DataList
     */
    public function getCurrencies()
    {
        return JobCurrency::get();
    }

    /**
     * @return string
     */
    public function getCurrencyParam()
    {
        return self::CURRENCY;
    }

    /**
     * @param string $currency
     * @return bool
     */
    public function IsSelectedCurrency(string $currency)
    {
        $currencyId = $this->getRequest()->getVar(self::CURRENCY);
        return ($currencyId === $currency);
    }

    /**
     * @return DataList
     */
    public function getSalaryFrequencies()
    {
        return JobSalaryFrequency::get();
    }

    /**
     * @return string
     */
    public function getSalaryFrequencyParam()
    {
        return self::SALARY_PER;
    }

    /**
     * @param string $salaryFrequency
     * @return bool
     */
    public function IsSelectedSalaryFrequency(string $salaryFrequency)
    {
        $salaryFrequencyId = $this->getRequest()->getVar(self::SALARY_PER);
        return ($salaryFrequencyId === $salaryFrequency);
    }

    /**
     * @return bool
     */
    public function getSalaryMinimum()
    {
        $salaryMinimum = $this->getRequest()->getVar(self::SALARY_MIN);
        return $salaryMinimum;
    }

    /**
     * @return bool
     */
    public function getSalaryMaximum()
    {
        $salaryMax = $this->getRequest()->getVar(self::SALARY_MAX);
        return $salaryMax;
    }

    /**
     * @return bool
     */
    public function getSalaryPer()
    {
        $salaryPer = $this->getRequest()->getVar(self::SALARY_PER);
        return $salaryPer;
    }

    /**
     * @return bool
     */
    public function IsSelectedSalaryFields()
    {
        $currencyId = $this->getRequest()->getVar(self::CURRENCY);

        if ($currencyId !== null && $currencyId !== 'All') {
            return true;
        }

        $salaryMin = $this->getRequest()->getVar(self::SALARY_MIN);

        if ($salaryMin !== null && $salaryMin !== '') {
            return true;
        }

        $salaryMax = $this->getRequest()->getVar(self::SALARY_MAX);

        if ($salaryMax !== null && $salaryMax !== '') {
            return true;
        }

        $salaryPer = $this->getRequest()->getVar(self::SALARY_PER);

        if ($salaryPer !== null && $salaryPer !== 'Any') {
            return true;
        }

        return false;
    }

    /**
     * @param $parameter
     * @return bool
     */
    public function isParameterSet($parameter)
    {
        return (!is_null($parameter) && $parameter !== 0  && $parameter !== '0' && $parameter !== '' && $parameter !== 'Any' && $parameter !== 'All');
    }
}
