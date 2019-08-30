<?php
declare(strict_types=1);

namespace Atomicptr\Lscache\Service;

use Atomicptr\Lscache\Rules\CacheableRuleInterface;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class CacheableRulesParserService implements SingletonInterface {

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct() {
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(self::class);
    }

    /**
     * @param int $statusCode
     * @param TypoScriptFrontendController $tsfe
     * @return bool
     */
    public function isCacheable(int $statusCode, TypoScriptFrontendController $tsfe) : bool {
        foreach ($this->getConfiguratedRules() as $ruleClassName) {
            $rule = GeneralUtility::makeInstance($ruleClassName);

            if ($rule instanceof CacheableRuleInterface && !$rule->isCacheable($statusCode, $tsfe)) {
                $this->logger->warning(
                    "Could not cache page because lscache rule \"".$rule->getName()."\" was violated!"
                );

                return false;
            }
        }

        return true;
    }

    protected function getConfiguratedRules() : array {
        return $GLOBALS["TYPO3_CONF_VARS"]["EXTCONF"]["lscache"]["rules"] ?? [];
    }
}
