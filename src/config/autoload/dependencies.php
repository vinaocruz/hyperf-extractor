<?php

declare(strict_types=1);

use App\Service\FileReaderService;
use Domain\Service\ReaderServiceInterface;

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    ReaderServiceInterface::class => FileReaderService::class
];
