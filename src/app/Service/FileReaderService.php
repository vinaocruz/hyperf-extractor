<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Negotiation;
use App\Domain\Service\ReaderServiceInterface;
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
        $part = explode(';', $line);

        return sprintf(
            '%s;%s;%s;%s;%s',
            $part[5],
            $part[8] ?? '',
            $part[1],
            (float)$part[3],
            (int)$part[4]
        );

        // $negotiation = new Negotiation();
        // $negotiation->closedat = $part[5];
        // $negotiation->transationat = $part[8] ?? null;
        // $negotiation->ticketcode = $part[1];
        // $negotiation->price = (float) $part[3];
        // $negotiation->quantity = (int) $part[4];

        // return $negotiation;
    }
}