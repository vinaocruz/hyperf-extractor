<?php

declare(strict_types=1);

namespace App\Domain\Service;

use Swoole\Coroutine\Channel;

interface ReaderServiceInterface
{
    public function read(string $file, Channel &$channel);
}