<?php

declare(strict_types=1);

namespace App\Repository;
use App\Domain\Repository\NegotiationRepository;

class PostgresNegotiationRepository implements NegotiationRepository
{
    private $connection = null;

    private function connect()
    {
        if ($this->connection === null) {
            $this->connection = pg_connect(
                sprintf(
                    "host=%s dbname=%s user=%s password=%s",
                    getenv('DB_HOST'),
                    getenv('DB_DATABASE'),
                    getenv('DB_USERNAME'),
                    getenv('DB_PASSWORD'),
                )
            ) or die('Could not connect: ' . pg_last_error());
        }
    }

    public function bulkImport(array $bulkImport): void
    {
        $this->connect();

        if ($this->connection === null) {
            throw new \RuntimeException('Connection not initialized');
        }

        $res = pg_copy_from($this->connection, 'public.negotiations', $bulkImport, ';');

        if ($res == false){
            throw new \RuntimeException('Failed to import bulk data');
        }
    }
}