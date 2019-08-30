<?php
declare(strict_types=1);

namespace Atomicptr\Lscache\Service;

use Atomicptr\Lscache\Constants\Cacheability;
use Atomicptr\Lscache\Constants\LscacheHeaders;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class LscacheService implements SingletonInterface {

    /**
     * @var Dispatcher
     */
    protected $signalSlotDispatcher;

    /**
     * @var CacheableRulesParserService
     */
    protected $cacheableRulesParser;

    /**
     * @var array
     */
    public $headers = [];

    /**
     * @var array
     */
    public $cacheVariations = [];

    /**
     * @var array
     */
    public $cacheTags = [];

    /**
     * @var bool
     */
    public $canPurge = true;

    public function __construct() {
        $this->signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $this->cacheableRulesParser = GeneralUtility::makeInstance(CacheableRulesParserService::class);
    }

    /**
     * @param int $statusCode
     * @param TypoScriptFrontendController $tsfe
     * @return array
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    public function getCacheResponseHeaders(int $statusCode, TypoScriptFrontendController $tsfe) : array {
        $this->headers = [];

        if ($this->cacheableRulesParser->isCacheable($statusCode, $tsfe)) {
            $this->headers = [
                LscacheHeaders::CACHE_CONTROL => $this->getCacheControlHeader($tsfe),
                LscacheHeaders::TAG => $this->getCacheTagsHeader($tsfe),
            ];

            $varyTags = $this->getCacheVariationTags($tsfe);

            if (!empty($varyTags)) {
                $this->headers[LscacheHeaders::VARY] = implode(",", $varyTags);
            }
        }

        if (empty($this->headers)) {
            $this->headers = $this->getResponseHeadersForNoCache();
        }

        $this->signalSlotDispatcher->dispatch(__CLASS__, "cacheResponseHeaders", [$tsfe, $this]);

        return $this->headers;
    }

    public function getResponseHeadersForNoCache() : array {
        $config = $this->getConfigurationService();
        $esiEnabled = $this->getEsiEnabledString($config->isEsiEnabled());

        return [
            LscacheHeaders::CACHE_CONTROL => Cacheability::NO_CACHE.",max-age=0,$esiEnabled"
        ];
    }

    protected function getCacheControlHeader(?TypoScriptFrontendController $tsfe) : string {
        $config = $this->getConfigurationService();

        $ttl = $tsfe->get_cache_timeout() ?? 0;
        $cacheability = "public";
        $esiEnabled = $this->getEsiEnabledString($config->isEsiEnabled());

        return "${cacheability},max-age=$ttl${esiEnabled}";
    }

    protected function getEsiEnabledString(bool $esiEnabled) : string {
        if ($esiEnabled) {
            return ",esi=on";
        }

        return "";
    }

    /**
     * @param TypoScriptFrontendController $tsfe
     * @return string
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    protected function getCacheTagsHeader(TypoScriptFrontendController $tsfe) : string {
        $tsfe->determineId();
        $pageUid = $tsfe->id;

        $this->cacheTags = $tsfe->getPageCacheTags();

        if (!empty($pageUid) && !in_array("pageId_$pageUid", $this->cacheTags)) {
            $this->cacheTags[] = "pageId_$pageUid";
        }

        $this->signalSlotDispatcher->dispatch(__CLASS__, "cacheTags", [$tsfe, $this]);

        return implode(",", $this->cacheTags);
    }

    /**
     * @param TypoScriptFrontendController $tsfe
     * @return array
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     */
    protected function getCacheVariationTags(TypoScriptFrontendController $tsfe) : array {
        $this->cacheVariations = [];

        $this->signalSlotDispatcher->dispatch(__CLASS__, "cacheVariations", [$tsfe, $this]);

        return $this->cacheVariations;
    }

    public function purge(string $purgeIdentifier) : void {
        $this->canPurge = true;

        $this->signalSlotDispatcher->dispatch(__CLASS__, "beforePurge", [$purgeIdentifier, $this]);

        if ($this->canPurge) {
            header(LscacheHeaders::PURGE . ": $purgeIdentifier");
        }
    }

    public function purgeAll() : void {
        $this->purge("*");
    }

    public function purgePage(int $pageUid) : void {
        $this->purge("tag=pageId_$pageUid");
    }

    protected function getContext() : Context {
        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);
        return $context;
    }

    protected function getConfigurationService() : ConfigurationService {
        /** @var ConfigurationService $configurationService */
        $configurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        return $configurationService;
    }
}
