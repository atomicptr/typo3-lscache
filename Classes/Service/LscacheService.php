<?php
declare(strict_types=1);

namespace Atomicptr\Lscache\Service;

use Atomicptr\Lscache\Constants\Cacheability;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class LscacheService implements SingletonInterface {

    const LSCACHE_CACHE_CONTROL_HEADER = "X-LiteSpeed-Cache-Control";
    const LSCACHE_CACHE_TAG_HEADER = "X-LiteSpeed-Tag";
    const LSCACHE_CACHE_VARY_HEADER = "X-LiteSpeed-Vary";
    const LSCACHE_PURGE_HEADER = "X-LiteSpeed-Purge";

    public function getCacheResponseHeaders(int $statusCode, TypoScriptFrontendController $tsfe) : array {
        $headers = [];

        // TODO: add ability to define custom "Rules"
        if ($tsfe->isStaticCacheble()) {
            $headers = [
                self::LSCACHE_CACHE_CONTROL_HEADER => $this->getCacheControlHeader($tsfe),
                self::LSCACHE_CACHE_TAG_HEADER => $this->getCacheTagsHeader($tsfe),
            ];

            $varyTags = $this->getCacheVariationTags($tsfe);

            if (!empty($varyTags)) {
                $headers[self::LSCACHE_CACHE_VARY_HEADER] = implode(",", $varyTags);
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
            self::LSCACHE_CACHE_CONTROL_HEADER => Cacheability::NO_CACHE.",max-age=0,$esiEnabled"
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
        header(self::LSCACHE_PURGE_HEADER.": $purgeIdentifier");
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