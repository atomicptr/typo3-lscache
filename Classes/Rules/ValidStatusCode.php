<?php
declare(strict_types=1);

namespace Atomicptr\Lscache\Rules;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class ValidStatusCode implements CacheableRuleInterface {

    /**
     * Name of the Cachable rule
     * @return string
     */
    public function getName(): string {
        return self::class;
    }

    /**
     * Can the request be cached?
     * @param int $statusCode
     * @param TypoScriptFrontendController $tsfe
     * @return bool
     */
    public function isCacheable(int $statusCode, TypoScriptFrontendController $tsfe): bool {
        return $statusCode >= 200 && $statusCode < 299;
    }
}
