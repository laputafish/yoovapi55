<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\TaxFormHelper;

class generateTaxForms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:tax_forms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generation of Tax Forms';

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
      TaxFormHelper::checkPending();
    }
}
