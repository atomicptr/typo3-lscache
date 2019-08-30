<?php
declare(strict_types=1);

namespace Atomicptr\Lscache\Middleware;

use Atomicptr\Lscache\Service\LscacheService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class LscacheMiddleware implements MiddlewareInterface {

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $response = $handler->handle($request);
        return $this->responseWithCacheHeadersIfApplicable($response);
    }

    protected function isDebugModeEnabled() : bool {
        return false;
    }

    protected function responseWithCacheHeadersIfApplicable(ResponseInterface $response) : ResponseInterface {
        /** @var LscacheService $cacheService */
        $cacheService = GeneralUtility::makeInstance(LscacheService::class);

        $headers = $cacheService->getCacheResponseHeaders($response->getStatusCode(), $GLOBALS["TSFE"]);

        if ($this->isDebugModeEnabled()) {
            $headers = [];
            // TODO: maybe log headers?
        }

        return $this->responseWithAddedHeaders($response, $headers);
    }

    protected function responseWithAddedHeaders(ResponseInterface $response, array $headers) : ResponseInterface {
        foreach ($headers as $key => $value) {
            $response = $response->withAddedHeader($key, $value);
        }

        return $response;
    }


}
