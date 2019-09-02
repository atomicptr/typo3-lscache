<?php
declare(strict_types=1);

namespace Atomicptr\Lscache\Rules;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class IsStaticCacheable implements CacheableRuleInterface {

    /**
     * Can the request be cached?
     * @param int $statusCode
     * @param TypoScriptFrontendController $tsfe
     * @return bool
     */
    public function isCacheable(int $statusCode, TypoScriptFrontendController $tsfe): bool {
        return $tsfe->isStaticCacheble();
    }
}
