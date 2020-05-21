<?php

namespace Foris\Easy\Console\Tests;

use Foris\Easy\Console\Application;
use Foris\Easy\Console\Commands\Command;
use Foris\Easy\Console\Commands\GenerateCommand;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class ApplicationTest
 */
class ApplicationTest extends TestCase
{
    /**
     * Test get the app root path.
     *
     * @throws \ReflectionException
     */
    public function testGetRootPath()
    {
        $this->assertEquals($this->vfs()->url(), $this->app()->getRootPath());

        $this->app()->setRootPath('root_path');
        $this->assertEquals('root_path', $this->app()->getRootPath());

    }

    /**
     * Test get the app root namespace.
     */
    public function testGetRootNamespace()
    {
        $this->assertEquals('Project', $this->app()->getRootNamespace());

        $class = '\Project\Application';
        $this->assertEquals('Project', call_user_func([new $class, 'getRootNamespace']));

        $this->app()->setRootNamespace('root_namespace');
        $this->assertEquals('root_namespace', $this->app()->getRootNamespace());
    }

    /**
     * Test get command parent class.
     */
    public function testGetCommandParentClass()
    {
        $config = [
            'parent_class' => [
                Command::class => 'TestParentClass',
            ],
        ];

        $app = new Application($config);
        $this->assertEquals('TestParentClass', $app->getCommandParentClass(Command::class));
        $this->assertEquals(GenerateCommand::class, $app->getCommandParentClass(GenerateCommand::class));
    }

    /**
     * Test load command from paths.
     *
     * @throws \ReflectionException
     */
    public function testLoadCommandFromPaths()
    {
        $this->app()->load(null);
        $this->assertFalse($this->app()->has('test:hello'));


        $this->app()->load($this->vfs()->url() . '/src/Console/Commands');
        $this->assertTrue($this->app()->has('test:hello'));
    }

    /**
     * Get application mock instance
     *
     * @return \Mockery\Mock|Application
     */
    protected function mockApplication()
    {
        $app = \Mockery::mock(Application::class)->makePartial();
        $app->shouldReceive('has')->with('test:command')->andReturnTrue();
        $app->shouldReceive('run')->andReturn('command run success');
        $app->shouldReceive('has')->with('test:not-exist-command')->andReturnFalse();

        return $app;
    }

    /**
     * Test run an Artisan console command by name.
     *
     * @throws \Exception
     */
    public function testRunAnArtisanConsoleCommandByName()
    {
        $output = \Mockery::mock(BufferedOutput::class);
        $output->shouldReceive('fetch')->andReturn('get output content success');

        $app = $this->mockApplication();
        $result = $app->call('test:command', ['name' => 'test', '--force' => true], $output);
        $this->assertEquals('command run success', $result);

        $this->assertEquals('get output content success', $app->output());

        $this->expectException(CommandNotFoundException::class);
        $this->expectExceptionMessage('The command "test:not-exist-command" does not exist.');
        $app->call('test:not-exist-command');
    }
}
