<?php

namespace Foris\Easy\Console\Commands;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class Command
 */
abstract class Command extends SymfonyCommand
{
    use InteractsWithIO;

    /**
     * Command name
     *
     * @var string
     */
    protected $name = '';

    /**
     * Command description
     *
     * @var string
     */
    protected $description = '';

    /**
     * Help message
     *
     * @var string
     */
    protected $help = '';

    /**
     * Gets the application instance for this command.
     *
     * @return null|\Symfony\Component\Console\Application|\Foris\Easy\Console\Application
     */
    public function getApplication()
    {
        return parent::getApplication();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setInput($input);
        $this->setOutput(new SymfonyStyle($input, $output));
        $this->handle();

        return 0;
    }

    /**
     * Execute the console command.
     */
    abstract protected function handle();

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        foreach ($this->getArguments() as $arguments) {
            call_user_func_array([$this, 'addArgument'], $arguments);
        }

        foreach ($this->getOptions() as $options) {
            call_user_func_array([$this, 'addOption'], $options);
        }

        $this->setName($this->name)->setDescription($this->description)->setHelp($this->help);
    }
}
