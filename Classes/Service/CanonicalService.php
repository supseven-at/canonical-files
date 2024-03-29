<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Supseven\CanonicalFiles\Service;

use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\Mime\MimeTypes;
use TYPO3\CMS\Core\ExpressionLanguage\Resolver;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class to create a canonical file uri
 */
class CanonicalService
{
    /**
     * Simple runtime cache for base URI
     *
     * @var array
     */
    protected static array $baseUriCache = [];

    public function __construct(
        protected readonly SiteFinder $siteFinder,
        protected readonly Resolver $expressionLanguageResolver
    ) {
    }

    /**
     * Try to get the setting "tx_canonical_files_site_identifier" from the file storage record and create the
     * uri to the file accordingly.
     *
     * The key might be empty if not defined in the backend, or the file might come from a fallback storage. Then
     * we use the current base URL, if absolute is true.
     *
     * @param \TYPO3\CMS\Core\Resource\FileInterface $file
     * @return string
     */
    public function getFileUri(FileInterface $file): string
    {
        $fileUrl = $file->getPublicUrl();
        $fileUrl = str_starts_with($fileUrl, '/') ? $fileUrl : '/' . $fileUrl;
        $fileStorage = $file->getStorage()->getStorageRecord();

        // If configuration is available and set, create and use the canonical url...
        if (!empty($fileStorage['tx_canonical_files_site_identifier'])) {

            return $this->getBaseFromSiteIdentifier($fileStorage['tx_canonical_files_site_identifier']) . $fileUrl;
        }

        // ...otherwise use the current base URL
        return GeneralUtility::locationHeaderUrl($fileUrl);
    }

    /**
     * Extracts the base URL from the site configuration.
     *
     * Value is cached per site identifier.
     * URI is always returned without trailing slash, because the path to the file always starts with a slash.
     *
     * @param string $siteIdentifier
     * @return string
     */
    public function getBaseFromSiteIdentifier(string $siteIdentifier): string
    {
        if (isset(self::$baseUriCache[$siteIdentifier])) {
            return self::$baseUriCache[$siteIdentifier];
        }

        $siteConfiguration = $this->siteFinder->getAllSites()[$siteIdentifier]->getConfiguration();
        $baseFromSiteIdentifier = '';

        if (isset($siteConfiguration['baseVariants'])) {
            // Iterate over available base variants and evaluate their conditions
            foreach ($siteConfiguration['baseVariants'] as $baseVariant) {
                try {
                    if ($this->expressionLanguageResolver->evaluate($baseVariant['condition'])) {
                        $baseFromSiteIdentifier = $baseVariant['base'];
                        break;
                    }
                } catch (SyntaxError $e) {
                    // Ignore not applicable conditions and fail silently
                }
            }
        }

        // Use default base, if no variants match the current environment
        if ($baseFromSiteIdentifier === '') {
            $baseFromSiteIdentifier = $siteConfiguration['base'];
        }

        // Cache and return the result
        return self::$baseUriCache[$siteIdentifier] = rtrim($baseFromSiteIdentifier, '/');
    }

    /**
     * Create file headers
     *
     * @param string $filePath
     * @return \TYPO3\CMS\Core\Http\Response
     */
    public function buildResponseForFile(string $filePath): Response
    {
        $mimeType = new MimeTypes();
        $mimeType = $mimeType->guessMimeType($filePath);
        $response = new Response();

        return $response
            ->withHeader('Content-Length', (string)filesize($filePath))
            ->withHeader('Content-Type', $mimeType)
            ->withBody(new Stream($filePath));
    }
}
