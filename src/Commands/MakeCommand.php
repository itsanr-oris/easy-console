<?php

namespace Foris\Easy\Console\Commands;

use Symfony\Component\Console\Input\InputOption;

/**
 * Class MakeCommand
 */
class MakeCommand extends GenerateCommand
{
    /**
     * Command name
     *
     * @var string
     */
    protected $name = 'make:command';

    /**
     * Command description
     *
     * @var string
     */
    protected $description = 'Create a new console command';

    /**
     * Help message
     *
     * @var string
     */
    protected $help = '';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'command';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub(): string
    {
        if ($this->option('type') == 'generate-command') {
            return __DIR__ . '/../Stubs/DummyGenerateCommand.stub';
        }

        return __DIR__ . '/../Stubs/DummyCommand.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return parent::getDefaultNamespace($rootNamespace) . '\Commands';
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        return str_replace('dummy:command', $this->option('command'), parent::replaceClass($stub, $name));
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(),[
            ['type', 't', InputOption::VALUE_OPTIONAL, 'The command type that should be generate', 'command'],

            ['command', 'c', InputOption::VALUE_OPTIONAL, 'The terminal command that should be assigned', 'command:name'],
        ]);
    }
}
