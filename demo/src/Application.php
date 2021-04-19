<?php

namespace Foris\Easy\Console\Demo;

/**
 * Class Application
 */
class Application extends \Foris\Easy\Console\Application
{
    /**
     * Register the commands for the application.
     *
     * @throws \ReflectionException
     */
    protected function commands()
    {
        parent::commands();
        $this->load(__DIR__ . '/Commands');
    }
}
