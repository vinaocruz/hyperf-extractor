<?php

declare(strict_types=1);

namespace App\Command;

use Domain\Service\ReaderServiceInterface;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Coroutine\Parallel;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine\Channel;
use Symfony\Component\Stopwatch\Stopwatch;
use ZipArchive;

use function Hyperf\Coroutine\go;

#[Command]
class dataImport extends HyperfCommand
{

    #[Inject]
    private ReaderServiceInterface $readerService;

    public function __construct(protected ContainerInterface $container)
    {
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

        go(function() use ($batchCh) {
            while($data = $batchCh->pop()) {
                //TODO bulk import to database
                $this->line($data, 'info');
            }
        });

        $parallel->wait();
        $batchCh->close();

        //TODO create index

        $event = $stopWatch->stop('dataImport');
        $this->line(sprintf('Finished! Time: %.2f ms', $event->getDuration()), 'success');
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
