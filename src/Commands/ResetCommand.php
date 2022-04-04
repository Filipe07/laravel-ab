<?php

namespace AbTesting\Commands;

use AbTesting\Models\DatabaseVisitor;
use AbTesting\Models\Goal;
use AbTesting\Models\Variant;
use Illuminate\Console\Command;

class ResetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ab:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes all variants visitors and goal completions';

    /**
     * Create a new command instance.
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
        Goal::truncate();
        Variant::truncate();
        DatabaseVisitor::truncate();

        $this->info('Successfully deleted all variants visitors and goal completions.');
    }
}
