<?php

declare(strict_types=1);

use Supseven\CanonicalFiles\Backend\SiteSelectItemsProcessor;

(static function ($extKey, $table): void {
    $languageFileBePrefix = 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_db.xlf:';

    $additionalColumns = [
        // Add select field to select from site configuration
        'tx_canonical_files_site_identifier' => [
            'exclude'     => 1,
            'label'       => $languageFileBePrefix . 'sys_file_storage.tx_canonical_files_site',
            'description' => $languageFileBePrefix . 'sys_file_storage.tx_canonical_files_description',
            'config'      => [
                'type'            => 'select',
                'renderType'      => 'selectSingle',
                'itemsProcessors' => [
                    10 => [
                        'class' => SiteSelectItemsProcessor::class,
                    ],
                ],
            ],
        ],
    ];

    // Add field to new tab
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns($table, $additionalColumns);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        $table,
        '--div--;' . $languageFileBePrefix . 'sys_file_storage.tab-title,tx_canonical_files_site_identifier',
        '',
        'after:processingfolder'
    );
})('canonical_files', 'sys_file_storage');
