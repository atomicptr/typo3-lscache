# typo3-lscache

[LiteSpeed](https://www.litespeedtech.com/) Cache extension for TYPO3.

## Install via composer

```bash
$ composer require atomicptr/lscache
```

## Configuration

Put this inside your .htaccess

```xml
<IfModule LiteSpeed>
  CacheLookup public on
</IfModule>
```

## Signals & Slots

This extension has a few slots you can connect to:

### Class: \Atomicptr\Lscache\Service\LscacheService

#### cacheResponseHeaders

```php
<?php
// ...
class CacheResponseHeaderSlot {
    public function handle(LscacheService $lscacheService) {
        $lscacheService->headers = []; // remove headers
    }
}
```

#### cacheTags

```php
<?php
// ...
class CacheTagsSlot {
    public function handle(TypoScriptFrontendController $tsfe, LscacheService $lscacheService) {
        $lscacheService->cacheTags[] = "my_fancy_cachetag";
    }
}
```

#### cacheVariations


```php
<?php
// ...
class CacheVariationsSlot {
    public function handle(TypoScriptFrontendController $tsfe, LscacheService $lscacheService) {
        $lscacheService->cacheVariations[] = "cookie=my_variation_cookie";
    }
}
```

#### beforePurge

```php
<?php
// ...
class BeforePurgeSlot {
    public function handle(string $purgeIdentifier, LscacheService $lscacheService) {
        if ($purgeIdentifier === "*") {
            // Don't allow to purge everything (for some reason)
            $lscacheService->canPurge = false;
        }
    }
}
```

## License

MPL v2
