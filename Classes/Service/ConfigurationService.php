<?php
declare(strict_types=1);

namespace Atomicptr\Lscache\Service;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationService implements SingletonInterface {

    const SETTINGS_ENABLE_ESI = "lscacheSettingsEnableEsi";

    /**
     * @var array
     */
    protected $extConf;

    public function __construct() {
        $this->extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get("lscache");
    }

    public function isEsiEnabled() : bool {
        if (!$this->hasConfiguration(self::SETTINGS_ENABLE_ESI)) {
            return false;
        }

        return (bool)$this->getConfiguration(self::SETTINGS_ENABLE_ESI);
    }

    protected function hasConfiguration(string $key) : bool {
        return array_key_exists($key, $this->extConf);
    }

    protected function getConfiguration(string $key) {
        return $this->extConf[$key];
    }
}
