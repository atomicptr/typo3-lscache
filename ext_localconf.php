<?php

defined("TYPO3_MODE") or die();

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] =
    \Atomicptr\Lscache\Hooks\CacheUpdateHook::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']["lscache"] =
    \Atomicptr\Lscache\Hooks\ClearCacheHook::class."->clearCachePostProc";

$GLOBALS["TYPO3_CONF_VARS"]["EXTCONF"]["lscache"] = [
    "rules" => [
        \Atomicptr\Lscache\Rules\IsStaticCacheable::class,
        \Atomicptr\Lscache\Rules\NoIntScriptsIncluded::class,
        \Atomicptr\Lscache\Rules\ValidStatusCode::class,
    ]
];
