<?php

declare(strict_types=1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    App\Domain\Service\ReaderServiceInterface::class => App\Service\FileReaderService::class,
    App\Domain\Repository\NegotiationRepository::class => App\Repository\PostgresNegotiationRepository::class,
];
