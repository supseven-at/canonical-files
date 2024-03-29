<?php

declare(strict_types=1);

$EM_CONF[$_EXTKEY] = [
    'title'            => 'Canonical Files',
    'description'      => 'Extension to add canonical urls to all files',
    'category'         => 'fe',
    'author'           => 'Helmut Strasser',
    'author_email'     => 'office@supseven.at',
    'author_company'   => 'supseven',
    'state'            => 'stable',
    'clearCacheOnLoad' => true,
    'constraints'      => [
        'depends' => [
            'typo3' => '12.4.1-12.4.99',
        ],
    ],
];
