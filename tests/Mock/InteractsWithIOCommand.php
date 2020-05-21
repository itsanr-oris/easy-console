<?php

namespace Foris\Easy\Console\Tests\Mock;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Command
 */
class InteractsWithIOCommand extends \Foris\Easy\Console\Commands\Command
{
    /**
     * Command name
     *
     * @var string
     */
    protected $name = 'test:command';

    /**
     * Command description
     *
     * @var string
     */
    protected $description = 'This is a test command.';

    /**
     * Help message
     *
     * @var string
     */
    protected $help = '';

    public function __construct(?string $name = null)
    {
        parent::__construct($name);
        $this->setVerbosity('normal');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['test-argument', InputArgument::REQUIRED, 'This is a test argument.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(),[
            ['test-option', null, InputOption::VALUE_NONE, 'This is a test option.'],
        ]);
    }

    /**
     * Execute the console command.
     */
    protected function handle()
    {

    }
}
