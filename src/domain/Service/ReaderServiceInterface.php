<?php

declare(strict_types=1);

namespace Domain\Service;

use Swoole\Coroutine\Channel;

interface ReaderServiceInterface
{
    public function read(string $file, Channel &$channel);
}