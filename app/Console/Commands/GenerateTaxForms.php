<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Helpers\TaxFormHelper;
use App\Models\Form;

class GenerateTaxForms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:taxforms';

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
