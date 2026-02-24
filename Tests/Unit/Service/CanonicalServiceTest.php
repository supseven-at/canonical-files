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

namespace Supseven\CanonicalFiles\Tests\Unit\Service;

use JetBrains\PhpStorm\NoReturn;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Supseven\CanonicalFiles\Service\CanonicalService;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;

#[CoversClass(CanonicalService::class)]
final class CanonicalServiceTest extends TestCase
{
    /**
     * @throws \TYPO3\CMS\Core\Exception|\PHPUnit\Framework\MockObject\Exception
     */
    #[Test]
    #[NoReturn] public function getFileUri(): void
    {
        $context = 'Production/Test';
        $filePath = 'fileadmin/test.pdf';
        $testDomain = 'https://example.org/';

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

        $file = $this->createMock(FileInterface::class);
        $file->expects($this->once())->method('getPublicUrl')->willReturn($filePath);
        $resourceStorage = $this->createMock(ResourceStorage::class);
        $resourceStorage->expects($this->once())->method('getStorageRecord')->willReturn(['tx_canonical_files_site_identifier' => 'test']);
        $file->expects($this->once())->method('getStorage')->willReturn($resourceStorage);

        $base = new Uri($testDomain);
        $site = $this->createMock(Site::class);
        $site->expects($this->once())->method('getBase')->willReturn($base);
        $siteFinder = $this->createMock(SiteFinder::class);
        $siteFinder->expects($this->once())->method('getSiteByIdentifier')->with(self::equalTo('test'))->willReturn($site);

        $canonicalService = new CanonicalService($siteFinder);
        $this->assertSame($testDomain . $filePath, $canonicalService->getFileUri($file));
    }
}
