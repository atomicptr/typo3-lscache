<?php
declare(strict_types=1);

namespace Atomicptr\Lscache\Hooks;

use Atomicptr\Lscache\Service\LscacheService;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ClearCacheHook {

    /**
     * @var LscacheService
     */
    protected $lscacheService;

    public function __construct() {
        $this->lscacheService = GeneralUtility::makeInstance(LscacheService::class);
    }

    public function clearCachePostProc(array $params, DataHandler $dataHandler) : void {
        $cacheCmd = $params["cacheCmd"];

        if ($cacheCmd === "pages" || $cacheCmd === "all") {
            $this->lscacheService->purgeAll();
        }
    }
}
