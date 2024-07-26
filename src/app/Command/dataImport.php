<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Coroutine\Parallel;
use Psr\Container\ContainerInterface;
use ZipArchive;

#[Command]
class dataImport extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('app:dataImport');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Hyperf Demo Command');
    }

    private function extractor(string $file)
    {
        $this->line(sprintf('Extracting zip file %s...', $file), 'info');
        $zip = new ZipArchive();

        if ($zip->open($file)) {
            $zip->extractTo(pathinfo($file, PATHINFO_DIRNAME));
        }

        $zip->close();
    }

    public function handle()
    {
        $parallel = new Parallel();

        $files = glob(__DIR__.'/../../storage/example/*.zip');
        foreach ($files as $file) {
            $parallel->add(function () use ($file) {
                $this->extractor($file);
            });
        }
        $parallel->wait();
        
        $this->line('Finished!', 'success');
    }
}
