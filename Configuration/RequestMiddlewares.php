<?php

declare(strict_types=1);

use Supseven\CanonicalFiles\Middleware\AddCanonicalFileHeader;

return [
    'frontend' => [
        'supseven/canonical-files' => [
            'target' => AddCanonicalFileHeader::class,
            'after'  => [
                'typo3/cms-core/normalized-params-attribute',
            ],
            'before' => [
                'typo3/cms-frontend/eid',
            ],
        ],
    ],
];
