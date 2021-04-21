<?php

namespace Demo\Console\Commands;

use Foris\Easy\Console\Commands\Command;

/**
 * Class HelloCommand
 */
class HelloCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'test:hello';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * The console command help message.
     *
     * @var string
     */
    protected $help = '';

    /**
     * Execute the console command.
     */
    protected function handle()
    {
        parent::handle();
    }
}
