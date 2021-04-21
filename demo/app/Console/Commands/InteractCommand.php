<?php

namespace Demo\Console\Commands;

use Foris\Easy\Console\Commands\Command;

/**
 * Class InteractCommand
 */
class InteractCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'test:interact';

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
        $answer = $this->ask('This is a test question.', 'default');
        $this->line('The test question answer is : ' . $answer);
    }
}
