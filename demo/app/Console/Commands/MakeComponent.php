<?php

namespace Demo\Console\Commands;

use Foris\Easy\Console\Commands\GenerateCommand;

/**
 * Class MakeComponent
 */
class MakeComponent extends GenerateCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:component';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test make component command.';

    /**
     * The console command help message.
     *
     * @var string
     */
    protected $help = '';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../Stubs/DummyComponent.stub';
    }

    /**
     * Execute the console command.
     *
     * @return bool
     * @throws \Foris\Easy\Support\Exceptions\FileNotFountException
     * @throws \ReflectionException
     */
    protected function handle()
    {
        return parent::handle();
    }
}
