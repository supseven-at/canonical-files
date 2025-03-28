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

namespace Supseven\CanonicalFiles\Middleware;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Supseven\CanonicalFiles\Event\BeforeSendingFileResponseEvent;
use Supseven\CanonicalFiles\Service\CanonicalService;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Resource\ResourceFactory;

/**
 * Middleware to add a canonical link header to files
 *
 * Insert the following snippet into your .htaccess file to configure, which files will be affected by this middleware.
 * Amend the conditions accordingly:
 *
 * RewriteCond %{REQUEST_URI} ^/fileadmin
 * RewriteCond %{REQUEST_FILENAME} \.(pdf|doc|docx|xls|xlsx|ppt|pptx)$
 * RewriteRule ^.*$ %{ENV:CWD}index.php [QSA,L]
 */
readonly class AddCanonicalFileHeader implements MiddlewareInterface
{
    /**
     * Inject ResourceFactory
     *
     * @param \TYPO3\CMS\Core\Resource\ResourceFactory $resourceFactory
     * @param \Supseven\CanonicalFiles\Service\CanonicalService $canonicalService
     */
    public function __construct(
        private ResourceFactory $resourceFactory,
        private CanonicalService $canonicalService,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * If the current request handles a file, convert the URL of a file to a canonical URL
     *  and add it as a link header to the response.
     *
     * See class description above about which files affected.
     *
     * @throws \TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Get absolute file path (on file system) from request
        $uri = urldecode($request->getUri()->getPath());

        // Early return for explicit calls to index.php
        if (str_contains($uri, 'index.php')) {
            return $handler->handle($request);
        }

        // Check if file exists
        // If not we handle this as a common page request
        $requestedFile = Environment::getPublicPath() . $uri;

        if (is_file($requestedFile)) {
            // Retrieve FAL from file path to get information about the file's storage
            $file = $this->resourceFactory->retrieveFileOrFolderObject($uri);

            // Early return if FAL can not be built
            if (!$file) {
                return $handler->handle($request);
            }

            // We could compare the file's storage location with the current site's base URL and only add
            // the canonical link header if the file's location is not located in the current site's storage.
            // But it's okay to add the canonical link header in any case, so we save pains and just add the header.
            // Get the site identifier from the file's storage configuration, if available:
            $canonisedFileUrl = $this->canonicalService->getFileUri($file);

            // Build file headers manually
            $response = $this->canonicalService->buildResponseForFile($requestedFile);

            // Set the canonised header
            $response = $response->withHeader('Link', '<' . $canonisedFileUrl . '>; rel="canonical"');

            // Dispatch event and send the result
            return $this->eventDispatcher->dispatch(new BeforeSendingFileResponseEvent($file, $request, $response))->response;
        }

        return $handler->handle($request);
    }
}
