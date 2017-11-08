<?php

/**
 * This file is part of a marmalade GmbH project
 * It is not Open Source and may not be redistributed.
 * For contact information please visit http://www.marmalade.de
 * Version:    1.0
 * Author:     Jens Richter <richter@marmalade.de>
 * Author URI: http://www.marmalade.de
 */
class makaira_connect_oxviewconfig extends makaira_connect_oxviewconfig_parent
{
    protected static $makairaFilter = null;

    protected $activeFilter = null;

    protected $generatedFilterUrl = [];

    public function redirectMakairaFilter($baseUrl)
    {
        if (!oxRegistry::getUtils()->seoIsActive()) {
            return;
        }

        $useSeoFilter = $this->getConfig()->getShopConfVar(
            'makaira_connect_seofilter',
            null,
            oxConfig::OXMODULE_MODULE_PREFIX . 'makaira/connect'
        );

        if (!$useSeoFilter) {
            return;
        }

        $filterParams = $this->getConfig()->getRequestParameter('makairaFilter');

        // TODO Handle range filter in frontend and remove this
        if (!empty($filterParams)) {
            $filterParams = $this->filterRangeValues($filterParams);
        }

        $finalUrl = $this->generateSeoUrlFromFilter($baseUrl, $filterParams);

        oxRegistry::getUtils()->redirect($finalUrl, false, 302);
    }

    public function getAggregationFilter()
    {
        if (null !== $this->activeFilter) {
            return $this->activeFilter;
        }

        $this->activeFilter = [];
        $categoryId     = $this->getActCatId();
        $manufacturerId = $this->getActManufacturerId();
        $searchParam    = $this->getActSearchParam();
        $className      = $this->getActiveClassName();

        // get filter cookie
        $cookieFilter = $this->loadMakairaFilterFromCookie();
        // get filter from form submit
        $requestFilter = (array)oxRegistry::getConfig()->getRequestParameter('makairaFilter');

        if (!empty($requestFilter)) {
            // TODO Handle range filter in frontend and remove this
            $requestFilter = $this->filterRangeValues($requestFilter);

            $cookieFilter = $this->buildCookieFilter($className, $requestFilter, $categoryId, $manufacturerId, $searchParam);
            $this->saveMakairaFilterToCookie($cookieFilter);
            $this->activeFilter = $requestFilter;

            return $this->activeFilter;
        }

        if (empty($cookieFilter)) {
            return $this->activeFilter;
        }

        if (isset($searchParam) && 'search' == $className) {
            $this->activeFilter = isset($cookieFilter['search'][$searchParam]) ? $cookieFilter['search'][$searchParam] : [];
        } elseif (isset($categoryId)) {
            $this->activeFilter = isset($cookieFilter['category'][$categoryId]) ? $cookieFilter['category'][$categoryId] : [];
        } elseif (isset($manufacturerId)) {
            $this->activeFilter = isset($cookieFilter['manufacturer'][$manufacturerId]) ?
                $cookieFilter['manufacturer'][$manufacturerId] : [];
        }

        return $this->activeFilter;
    }

    public function resetMakairaFilter($type, $ident)
    {
        $cookieFilter = $this->loadMakairaFilterFromCookie();
        unset($cookieFilter[$type][$ident]);
        $this->saveMakairaFilterToCookie($cookieFilter);
    }

    public function getMakairaMainStylePath()
    {
        $modulePath = $this->getModulePath('makaira/connect') . '';
        $file       = glob($modulePath . 'out/dist/*.css');

        return substr(reset($file), strlen($modulePath));
    }

    public function getMakairaMainScriptPath()
    {
        $modulePath = $this->getModulePath('makaira/connect') . '';
        $file       = glob($modulePath . 'out/dist/*.js');

        return substr(reset($file), strlen($modulePath));
    }

    /**
     * @return array|mixed
     */
    private function loadMakairaFilterFromCookie()
    {
        if (null !== static::$makairaFilter) {
            return static::$makairaFilter;
        }
        $oxUtilsServer   = oxRegistry::get('oxUtilsServer');
        $rawCookieFilter = $oxUtilsServer->getOxCookie('makairaFilter');
        $cookieFilter    = !empty($rawCookieFilter) ? json_decode(base64_decode($rawCookieFilter), true) : [];

        static::$makairaFilter = (array)$cookieFilter;

        return static::$makairaFilter;
    }

    /**
     * @param $cookieFilter
     */
    public function saveMakairaFilterToCookie($cookieFilter)
    {
        static::$makairaFilter = $cookieFilter;
        $oxUtilsServer       = oxRegistry::get('oxUtilsServer');
        $oxUtilsServer->setOxCookie('makairaFilter', base64_encode(json_encode($cookieFilter)));
    }

    public function savePageNumberToCookie()
    {
        $pageNumber    = oxRegistry::getConfig()->getRequestParameter('pgNr');
        $oxUtilsServer = oxRegistry::get('oxUtilsServer');
        $oxUtilsServer->setOxCookie('makairaPageNumber', $pageNumber);
    }

    /**
     * @param $className
     * @param $requestFilter
     * @param $cookieFilter
     * @param $categoryId
     * @param $manufacturerId
     * @param $searchParam
     * @return mixed
     */
    public function buildCookieFilter($className, $requestFilter, $categoryId, $manufacturerId, $searchParam)
    {
        $cookieFilter = [];
        switch ($className) {
            case 'alist':
                $cookieFilter['category'][$categoryId] = $requestFilter;
                break;
            case 'manufacturerlist':
                $cookieFilter['manufacturer'][$manufacturerId] = $requestFilter;
                break;
            case 'search':
                $cookieFilter['search'][$searchParam] = $requestFilter;
                break;
        }
        return $cookieFilter;
    }

    /**
     * @param $baseUrl
     * @param $filterParams
     *
     * @return string
     */
    public function generateSeoUrlFromFilter($baseUrl, $filterParams)
    {
        if (isset($this->generatedFilterUrl[$baseUrl])) {
            return $this->generatedFilterUrl[$baseUrl];
        }

        if (empty($filterParams)) {
            return $baseUrl;
        }

        $useSeoFilter = $this->getConfig()->getShopConfVar(
            'makaira_connect_seofilter',
            null,
            oxConfig::OXMODULE_MODULE_PREFIX . 'makaira/connect'
        );
        if (!$useSeoFilter) {
            $this->generatedFilterUrl[$baseUrl] = $baseUrl;
            return $this->generatedFilterUrl[$baseUrl];
        }

        $path = [];
        foreach ($filterParams as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    $path[] = "{$key}_{$item}";
                }
            } else {
                $path[] = "{$key}_{$value}";
            }
        }
        $filterString = implode('/', $path);

        $parsedUrl = parse_url($baseUrl);

        $path       = rtrim($parsedUrl['path'], '/');
        $pageNumber = '';
        if (preg_match('#(.*)/(\d+)$#', $path, $matches)) {
            $path       = $matches[1];
            $pageNumber = $matches[2] . '/';
        }
        $path = implode('/', [$path, $filterString, $pageNumber]);

        $query = $parsedUrl['query'] ? "?{$parsedUrl['query']}" : "";

        $this->generatedFilterUrl[$baseUrl] = "{$parsedUrl['scheme']}://{$parsedUrl['host']}{$path}{$query}";

        return $this->generatedFilterUrl[$baseUrl];
    }

    /**
     * @param $filterParams
     *
     * @return array
     */
    private function filterRangeValues($filterParams)
    {
        // TODO Handle range filter in frontend and remove this
        foreach ($filterParams as $key => $value) {
            if (false !== ($pos = strrpos($key, '_to'))) {
                if (isset($filterParams[substr($key, 0, $pos) . '_rangemax'])) {
                    if ($value == $filterParams[substr($key, 0, $pos) . '_rangemax']) {
                        unset($filterParams[$key]);
                        continue;
                    }
                }
            }
            if (false !== ($pos = strrpos($key, '_from'))) {
                if (isset($filterParams[substr($key, 0, $pos) . '_rangemin'])) {
                    if ($value == $filterParams[substr($key, 0, $pos) . '_rangemin']) {
                        unset($filterParams[$key]);
                        continue;
                    }
                }
            }
        }
        $filterParams = array_filter(
            $filterParams,
            function ($key) {
                return (false === strrpos($key, '_rangemin')) && (false === strrpos($key, '_rangemax'));
            },
            ARRAY_FILTER_USE_KEY
        );

        return $filterParams;
    }
}
