<?php

declare(strict_types=1);

namespace Supseven\CanonicalFiles\Backend;

use TYPO3\CMS\Core\DataHandling\ItemsProcessorContext;
use TYPO3\CMS\Core\DataHandling\ItemsProcessorInterface;
use TYPO3\CMS\Core\Schema\Struct\SelectItemCollection;
use TYPO3\CMS\Core\Site\SiteFinder;

/**
 * Provider of sites for TCA select fields
 *
 * Like renderType `selectSingle` with sites as items.
 *
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
class SiteSelectItemsProcessor implements ItemsProcessorInterface
{
    public function __construct(
        protected readonly SiteFinder $siteFinder,
    ) {
    }

    public function processItems(SelectItemCollection $items, ItemsProcessorContext $context): SelectItemCollection
    {
        $itemList = [
            [
                'value' => '',
                'label' => '',
            ],
        ];

        foreach ($this->siteFinder->getAllSites() as $site) {
            $label = $site->getRawConfiguration()['websiteTitle'] ?? $site->getDefaultLanguage()->getWebsiteTitle();
            $itemList[] = [
                'value' => $site->getIdentifier(),
                'label' => $label,
            ];
        }

        return SelectItemCollection::createFromArray($itemList, 'select');
    }
}
