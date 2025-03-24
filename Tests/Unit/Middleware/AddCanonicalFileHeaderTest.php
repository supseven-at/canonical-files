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

namespace Supseven\CanonicalFiles\Tests\Unit\Middleware;

use JetBrains\PhpStorm\NoReturn;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Supseven\CanonicalFiles\Event\BeforeSendingFileResponseEvent;
use Supseven\CanonicalFiles\Middleware\AddCanonicalFileHeader;
use Supseven\CanonicalFiles\Service\CanonicalService;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(AddCanonicalFileHeader::class)]
final class AddCanonicalFileHeaderTest extends UnitTestCase
{
    /**
     * @throws \TYPO3\CMS\Core\Exception|\PHPUnit\Framework\MockObject\Exception
     */
    #[Test]
    #[NoReturn] public function process(): void
    {
        $context = 'Production/Test';

        Environment::initialize(
            new ApplicationContext($context),
            false,
            true,
            '/',
            '/public/',
            '/var/',
            '/config/',
            '/vendor/bin/typo3/',
            'UNIX'
        );

        $filePath = 'fileadmin/test.pdf';

        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->once())->method('getPath')->willReturn($filePath);

        $serverRequest = $this->createMock(ServerRequestInterface::class);
        $serverRequest->expects($this->once())->method('getUri')->willReturn($uri);

        $resourceFactory = $this->createMock(ResourceFactory::class);
        $fileResource = $this->createMock(FileInterface::class);
        $resourceFactory->expects($this->once())->method('retrieveFileOrFolderObject')->with($filePath)->willReturn($fileResource);

        $canonicalService = $this->createMock(CanonicalService::class);
        $canonicalService->expects($this->once())->method('getFileUri')->with($fileResource)->willReturn('http://localhost/' . $filePath);

        $serverResponse = $this->createMock(Response::class);
        $canonicalService->expects($this->once())->method('buildResponseForFile')->with(Environment::getPublicPath() . $filePath)->willReturn($serverResponse);
        $serverResponse->expects($this->once())->method('withHeader')->with('Link', '<http://localhost/' . $filePath . '>; rel="canonical"')->willReturn($serverResponse);

        $expectedEvent = new BeforeSendingFileResponseEvent($fileResource, $serverRequest, $serverResponse);
        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher->expects($this->once())->method('dispatch')->with($this->equalTo($expectedEvent))->willReturn($expectedEvent);

        $requestHandler = $this->createMock(RequestHandlerInterface::class);

        $subject = new AddCanonicalFileHeader($resourceFactory, $canonicalService, $eventDispatcher);
        $subject->process($serverRequest, $requestHandler);
    }
}

// Mock file_exists and is_file functions

namespace Supseven\CanonicalFiles\Middleware;

// Make file_exists always true
function file_exists($path): true
{
    return true;
}

// Make is_file always true
function is_file($file): true
{
    return true;
}
