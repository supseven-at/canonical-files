<?php

declare(strict_types=1);

call_user_func(
    function ($extKey, $table): void {

        $languageFileBePrefix = 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_db.xlf:';

        $additionalColumns = [
            // Add select field to select from site configuration
            'tx_canonical_files_site_identifier' => [
                'exclude'     => 1,
                'label'       => $languageFileBePrefix . 'sys_file_storage.tx_canonical_files_site',
                'description' => $languageFileBePrefix . 'sys_file_storage.tx_canonical_files_description',
                'config'      => [
                    'type'       => 'select',
                    'renderType' => 'selectSingle',
                    'items'      => [], // Will be filled below
                    'size'       => 1,
                    'maxitems'   => 1,
                ],
            ],
        ];

        // Access all site configurations and add them to the select field
        $site = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Site\SiteFinder::class);
        // Add empty item
        $additionalColumns['tx_canonical_files_site_identifier']['config']['items'][] = [
            'label' => '',
            'value' => '',
        ];
        // Add all site configs
        foreach ($site->getAllSites() as $siteItem) {
            $additionalColumns['tx_canonical_files_site_identifier']['config']['items'][] = [
                // Website title
                'label' => $siteItem->getConfiguration()['websiteTitle'],
                // For the value of the select option we take the site identifier instead of the actual domain,
                // so this feature is also working for different environments
                'value' => $siteItem->getIdentifier(),
            ];
        }

        // Add field to new tab
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns($table, $additionalColumns);
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
            $table,
            '--div--;' . $languageFileBePrefix . 'sys_file_storage.tab-title,tx_canonical_files_site_identifier',
            '',
            'after:processingfolder'
        );

    },
    'canonical-files',
    'sys_file_storage'
);
