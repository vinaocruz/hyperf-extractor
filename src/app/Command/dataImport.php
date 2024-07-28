<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Negotiation;
use App\Domain\Repository\NegotiationRepository;
use App\Domain\Service\ReaderServiceInterface;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Coroutine\Parallel;
use Hyperf\Coroutine\WaitGroup;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine\Channel;
use Symfony\Component\Stopwatch\Stopwatch;
use ZipArchive;

use function Hyperf\Coroutine\go;

#[Command]
class dataImport extends HyperfCommand
{
    private const BATCH_SIZE = 200000;

    public function __construct(
        protected ContainerInterface $container,
        private ReaderServiceInterface $readerService,
        private NegotiationRepository $negotiationRepository
    ) {
        parent::__construct('app:dataImport');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Hyperf Demo Command');
    }

    public function handle()
    {
        $stopWatch = new Stopwatch();
        $stopWatch->start('dataImport');

        $this->line("Starting...", 'info');

        $filesFolder = __DIR__.'/../../storage/example';

        $this->extract($filesFolder);

        $unzipedFiles = glob($filesFolder . '/*.txt');
        
        $parallel = new Parallel();
        $batchCh = new Channel(count($unzipedFiles)*10);

        foreach ($unzipedFiles as $file) {
            $this->line(sprintf('Reading file %s', $file), 'info');
            $parallel->add(function () use ($file, $batchCh) {
                $this->readerService->read(pathinfo($file, PATHINFO_BASENAME), $batchCh);
            });
        }

        go(function() use ($batchCh, $stopWatch) {
            $bulkImport = [];
            $countBulk = $id = 0;
            $wg = new WaitGroup();

            while($data = $batchCh->pop()) {
                $bulkImport[] = ++$id . ';' . $data;
                $countBulk++;

                if ($countBulk == self::BATCH_SIZE) {
                    $wg->add(1);
                    go(function() use ($bulkImport, &$wg) {
                        $this->bulkImport($bulkImport, Coroutine::id());
                        $wg->done();
                    });

                    $countBulk = 0;
                    unset($bulkImport);
                    $bulkImport = [];
                }
            }

            if ($countBulk > 0) {
                $wg->add(1);
                go(function() use ($bulkImport, &$wg) {
                    $this->bulkImport($bulkImport, Coroutine::id());
                    $wg->done();
                });
            }

            $wg->wait();

            $event = $stopWatch->stop('dataImport');
            $this->line(sprintf('Finished! Time: %.2f ms', $event->getDuration()), 'success');
        });

        $parallel->wait();
        $batchCh->close();
    }

    private function bulkImport(array $bulkImport, int $cid): void
    {
        $bulkImportTime = new Stopwatch();
        $bulkImportTime->start('bulkImport');

        $this->negotiationRepository->bulkImport($bulkImport);
        // Negotiation::insert($bulkImport);

        $eventBulk = $bulkImportTime->stop('bulkImport');
        $this->line(sprintf('Inserted %d rows in %.2f ms (CID: %d)', self::BATCH_SIZE, $eventBulk->getDuration(), $cid), 'info');
    }

    private function extract(string $filesFolder) {
        $parallel = new Parallel();

        $files = glob($filesFolder . '/*.zip');
        foreach ($files as $file) {
            $parallel->add(function () use ($file) {
                $this->line(sprintf('Extracting zip file %s...', $file), 'info');
                $zip = new ZipArchive();

                if ($zip->open($file)) {
                    $zip->extractTo(pathinfo($file, PATHINFO_DIRNAME));
                }

                $zip->close();
            });
        }

        $parallel->wait();
    }
}
