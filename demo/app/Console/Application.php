<?php

namespace Demo\Console;

/**
 * Class Application
 */
class Application extends \Foris\Easy\Console\Application
{
    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        parent::commands();
        $this->load(__DIR__ . '/Commands');
    }
}
