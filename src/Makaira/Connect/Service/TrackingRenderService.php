<?php

namespace Makaira\Connect\Service;

use Makaira\Connect\Exception\EmptyTrackingDataException;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Exception\ArticleInputException;
use OxidEsales\Eshop\Core\Exception\NoArticleException;

use OxidEsales\Eshop\Core\Registry;
use function get_class;
use function json_encode;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class TrackingRenderService
{
    private $trackingDataGenerator;

    private $config;

    private static $trackingData = null;

    public function __construct(Config $config)
    {
        $this->trackingDataGenerator = Registry::get('makaira_tracking_data_generator');
        $this->config = $config;
    }

    /**
     * @throws EmptyTrackingDataException
     * @throws NoArticleException
     * @throws ArticleInputException
     */
    public function render(): string
    {
        $trackingData = $this->getTrackingData();

        if (empty($trackingData)) {
            throw new EmptyTrackingDataException();
        }

        $trackerUrl = json_encode($this->trackingDataGenerator->getTrackerUrl());
        $trackingHtml = '<script type="text/javascript">var _paq = _paq || [];';

        foreach ($trackingData as $trackingPart) {
            $trackingHtml .= '_paq.push(' . json_encode($trackingPart) . ');';
        }

        $trackingHtml .= "var d=document, g=d.createElement('script'), ";
        $trackingHtml .= "s=d.getElementsByTagName('script')[0]; g.type='text/javascript';";
        $trackingHtml .= "g.defer=true; g.async=true; g.src={$trackerUrl}+'/piwik.js'; ";
        $trackingHtml .= "s.parentNode.insertBefore(g,s);";
        $trackingHtml .= '</script>';

        return $trackingHtml;
    }

    /**
     * @return array
     * @throws ArticleInputException
     * @throws NoArticleException
     */
    protected function getTrackingData(): array
    {
        if (null === self::$trackingData) {
            $oxidController     = $this->config->getTopActiveView();
            self::$trackingData = $this->trackingDataGenerator->generate(get_class($oxidController));
        }

        return self::$trackingData;
    }
}
