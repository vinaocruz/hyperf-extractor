<?php

declare(strict_types=1);

namespace App\Service;

use Domain\Service\ReaderServiceInterface;
use League\Flysystem\Filesystem;
use Swoole\Coroutine\Channel;
use Throwable;

class FileReaderService implements ReaderServiceInterface
{
    public function __construct(private Filesystem $filesystem) {
    }

    public function read(string $file, Channel &$channel): void
    {
        try {
            $resource = $this->filesystem->readStream($file);
        } catch (Throwable $e) {
            return;
        }

        $ignoreFirstLine = true;
        while (($line = fgets($resource)) !== false) {
            if ($ignoreFirstLine) {
                $ignoreFirstLine = false;
                continue;
            }

            $channel->push($this->parseLine($line));
        }

        fclose($resource);
    }

    private function parseLine(string $line): string
    {
        //TODO parse line
        return $line;
    }
}