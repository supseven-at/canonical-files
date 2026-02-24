<?php

declare(strict_types=1);

namespace Supseven\CanonicalFiles\Tests\Unit\Backend;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Supseven\CanonicalFiles\Backend\SiteSelectItemsProcessor;
use TYPO3\CMS\Core\DataHandling\ItemsProcessorContext;
use TYPO3\CMS\Core\Schema\Struct\SelectItemCollection;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Site\SiteFinder;

/**
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
#[CoversClass(SiteSelectItemsProcessor::class)]
class SiteSelectItemsProcessorTest extends TestCase
{
    #[Test]
    public function runProcessItems(): void
    {
        $site1Id = 'site-a';
        $site1Title = 'Site 1 website title';
        $site2Id = 'site-b';
        $site2Title = 'Site 2, default language';

        $site1 = $this->createStub(Site::class);
        $site1->method('getIdentifier')->willReturn($site1Id);
        $site1->method('getRawConfiguration')->willReturn(['websiteTitle' => $site1Title]);

        $site2Lang0 = $this->createStub(SiteLanguage::class);
        $site2Lang0->method('getWebsiteTitle')->willReturn($site2Title);

        $site2 = $this->createStub(Site::class);
        $site2->method('getIdentifier')->willReturn($site2Id);
        $site2->method('getRawConfiguration')->willReturn([]);
        $site2->method('getDefaultLanguage')->willReturn($site2Lang0);

        $siteFinder = $this->createStub(SiteFinder::class);
        $siteFinder->method('getAllSites')->willReturn([$site1, $site2]);

        $expected = SelectItemCollection::createFromArray([
            [
                'label' => '',
                'value' => '',
            ],
            [
                'label' => $site1Title,
                'value' => $site1Id,
            ],
            [
                'label' => $site2Title,
                'value' => $site2Id,
            ],
        ], 'select');

        $context = new ItemsProcessorContext(
            'sys_file_storage',
            'site',
            [],
            [],
            [],
            0,
            $this->createStub(Site::class),
            [],
            [],
        );
        $subject = new SiteSelectItemsProcessor($siteFinder);
        $actual = $subject->processItems(new SelectItemCollection(), $context);

        $this->assertEquals($expected, $actual);
    }
}
