<?php

namespace NextApps\PoeditorSync\Commands;

use Illuminate\Console\Command;
use NextApps\PoeditorSync\PoeditorSync;

class DownloadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poeditor:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download translations from poeditor';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        PoeditorSync::download();

        $this->info('All translations have been downloaded!');
    }
}
