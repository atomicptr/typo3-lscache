<?php

namespace Atomicptr\Lscache\Rules;

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

interface CacheableRuleInterface {

    /**
     * Name of the Cachable rule
     * @return string
     */
    public function getName() : string;

    /**
     * Can the request be cached?
     * @param int $statusCode
     * @param TypoScriptFrontendController $tsfe
     * @return bool
     */
    public function isCacheable(int $statusCode, TypoScriptFrontendController $tsfe) : bool;
}
