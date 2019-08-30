<?php
declare(strict_types=1);

namespace Atomicptr\Lscache\Hooks;

use Atomicptr\Lscache\Service\LscacheService;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CacheUpdateHook {

    /**
     * @var LscacheService
     */
    protected $lscacheService;

    public function __construct() {
        $this->lscacheService = GeneralUtility::makeInstance(LscacheService::class);
    }

    // phpcs:ignore
    public function processDatamap_afterDatabaseOperations(
        $status,
        $table,
        $recordId,
        array $updatedFields,
        DataHandler $dataHandler
    ) {
        if ($table === "pages") {
            $this->lscacheService->purgePage((int)$recordId);
        }

        if ($table === "tt_content") {
            $record = BackendUtility::getRecord($table, $recordId);

            if (is_array($record) && array_key_exists("pid", $record)) {
                $this->lscacheService->purgePage($record["pid"]);
            }
        }
    }
}
