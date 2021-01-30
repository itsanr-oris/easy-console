<?php /** @noinspection PhpMethodParametersCountMismatchInspection */

/** @noinspection PhpUndefinedClassInspection */

namespace Foris\Easy\Console\Tests;

use Foris\Easy\Console\Application;
use Foris\Easy\Support\Str;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class ApplicationTest
 */
class ApplicationTest extends TestCase
{
    /**
     * Test get app root namespace
     */
    public function testGetRootNamespace()
    {
        $class = get_class($this->app());
        $namespace = substr($class, 0, strrpos($class, '\\'));
        $this->assertEquals($namespace, $this->app()->getRootNamespace());
    }

    /**
     * Test set app root namespace.
     */
    public function testSetRootNamespace()
    {
        $namespace = 'root_namespace';
        $this->assertEquals($namespace, $this->app()->setRootNamespace($namespace)->getRootNamespace());
    }

    /**
     * Test get app root path.
     *
     * @throws \ReflectionException
     */
    public function testGetRootPath()
    {
        $this->assertEquals( Str::finish($this->vfs()->url(), '/'), $this->app()->getRootPath());
    }

    /**
     * Test set app root path.
     *
     * @throws \ReflectionException
     */
    public function testSetRootPath()
    {
        $this->assertEquals('root_path', $this->app()->setRootPath('root_path')->getRootPath());
    }

    /**
     * Test get the destination file path.
     *
     * @throws \ReflectionException
     */
    public function testGetPath()
    {
        $path = Str::start('test_path', Str::finish($this->vfs()->url(), '/'));
        $this->assertEquals($path, $this->app()->getPath('test_path'));
    }

    /**
     * Test get the source code file path.
     *
     * @throws \ReflectionException
     */
    public function testGetSrcPath()
    {
        $path = Str::start('src', Str::finish($this->vfs()->url(), '/'));
        $this->assertEquals($path, $this->app()->getSrcPath());
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

        $this->app()->load($this->vfs()->url() . '/src/Commands');
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
        $app->shouldReceive('has')->with('test:command')->andReturn(true);
        $app->shouldReceive('run')->andReturn('command run success');
        $app->shouldReceive('has')->with('test:not-exist-command')->andReturn(false);

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

        if (class_exists( 'PHPUnit\Runner\Version' )) {
            $this->expectException(CommandNotFoundException::class);
            $this->expectExceptionMessage('The command "test:not-exist-command" does not exist.');
        } else {
            $this->setExpectedException(CommandNotFoundException::class, 'The command "test:not-exist-command" does not exist.');
        }

        $app->call('test:not-exist-command');
    }
}
