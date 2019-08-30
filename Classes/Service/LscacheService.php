<?php
declare(strict_types=1);

namespace Atomicptr\Lscache\Service;

use Atomicptr\Lscache\Constants\Cacheability;
use Atomicptr\Lscache\Constants\LscacheHeaders;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class LscacheService implements SingletonInterface {

    /**
     * @var CacheableRulesParserService
     */
    protected $cacheableRulesParser;

    public function __construct() {
        $this->cacheableRulesParser = GeneralUtility::makeInstance(CacheableRulesParserService::class);
    }

    public function getCacheResponseHeaders(int $statusCode, TypoScriptFrontendController $tsfe) : array {
        $headers = [];

        if ($this->cacheableRulesParser->isCacheable($statusCode, $tsfe)) {
            $headers = [
                LscacheHeaders::CACHE_CONTROL => $this->getCacheControlHeader($tsfe),
                LscacheHeaders::TAG => $this->getCacheTagsHeader($tsfe),
            ];

            $varyTags = $this->getCacheVariationTags($tsfe);

            if (!empty($varyTags)) {
                $headers[LscacheHeaders::VARY] = implode(",", $varyTags);
            }
        }

        if (empty($headers)) {
            $headers = $this->getResponseHeadersForNoCache();
        }

        return $headers;
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

    protected function getCacheTagsHeader(TypoScriptFrontendController $tsfe) : string {
        $tsfe->determineId();
        $pageUid = $tsfe->id;

        $cacheTags = $tsfe->getPageCacheTags();

        if (!empty($pageUid) && !in_array("pageId_$pageUid", $cacheTags)) {
            $cacheTags[] = "pageId_$pageUid";
        }

        // TODO: add ability to define custom cache tags

        return implode(",", $cacheTags);
    }

    protected function getCacheVariationTags(TypoScriptFrontendController $tsfe) : array {
        $variations = [];

        /* TODO: should there even be a seperate cache for each user?
         if ($tsfe->isUserOrGroupSet()) {
            try {
                $variations[] = "feuser_".$this->getContext()->getPropertyFromAspect("frontend.user", "id");
            } catch (AspectNotFoundException $e) {
                ;
            }
        }*/

        // TODO: add ability to define custom cache variations

        return $variations;
    }

    public function purge(string $purgeIdentifier) : void {
        // TODO: add purge hook to add extra controls to it
        header(LscacheHeaders::PURGE.": $purgeIdentifier");
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
