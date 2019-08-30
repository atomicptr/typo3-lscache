<?php
declare(strict_types=1);

return [
    "frontend" => [
        "atomicptr/lscache" => [
            "target" => \Atomicptr\Lscache\Middleware\LscacheMiddleware::class,
            "after" => [
                "typo3/cms-frontend/output-compression"
            ]
        ]
    ]
];
