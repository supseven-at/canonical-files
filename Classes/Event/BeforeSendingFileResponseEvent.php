<?php

declare(strict_types=1);

namespace Supseven\CanonicalFiles\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Resource\FileInterface;

/**
 * Event that is dispatched right before the file response is sent
 *
 * @author Georg Großberger <g.grossberger@supseven.at>
 */
class BeforeSendingFileResponseEvent
{
    public function __construct(
        public readonly FileInterface $file,
        public readonly ServerRequestInterface $request,
        public ResponseInterface $response,
    ) {
    }
}
