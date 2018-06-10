<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\ScannedDocumentHelper;

class CheckScannedDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:scanned {mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check scanned documents fed from scanner';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      ScannedDocumentHelper::check($this->argument('mode'));
        //
    }
}
