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
     * Gets the `composer.json` file content.
     *
     * @return array
     */
    protected function getComposer()
    {
        return json_decode(file_get_contents($this->vfs()->url() . '/composer.json'), true);
    }

    /**
     * Test get app root namespace
     *
     * @throws \ReflectionException
     */
    public function testGetRootNamespace()
    {
        $composer = $this->getComposer();
        $this->assertArrayHasKey($this->app()->getRootNamespace(), $composer['autoload']['psr-4']);
    }

    /**
     * Test get app root path.
     *
     * @throws \ReflectionException
     */
    public function testGetRootPath()
    {
        $this->assertEquals(Str::finish($this->vfs()->url(), '/'), $this->app()->getRootPath());
        $this->assertFileEquals(__DIR__ . '/../', (new Application())->getRootPath());
    }

    /**
     * Test set app root path.
     *
     * @throws \ReflectionException
     */
    public function testSetRootPath()
    {
        $this->assertEquals('/root_path/', $this->app()->setRootPath('/root_path/')->getRootPath());
    }

    /**
     * Test get the destination file path.
     *
     * @throws \ReflectionException
     */
    public function testGetPath()
    {
        $path = $this->vfs()->url() . '/test_path';
        $this->assertEquals($path, $this->app()->getPath('test_path'));
    }

    /**
     * Test get the source code file path.
     *
     * @throws \ReflectionException
     */
    public function testGetSrcPath()
    {
        $path = $this->vfs()->url() . '/app/';
        $this->assertEquals($path, $this->app()->getSrcPath());
    }

    /**
     * Test load command from paths.
     */
    public function testLoadCommandFromPaths()
    {
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

        $this->setExpectedException(CommandNotFoundException::class, 'The command "test:not-exist-command" does not exist.');
        $app->call('test:not-exist-command');
    }

    /**
     * Test user interacts.
     *
     * @throws \Exception
     */
    public function testUserInteracts()
    {
        $this->setInteractive(true);

        $this->setInputs(['']);
        $this->call('test:interact');
        $this->assertHasSubString('The test question answer is : default', $this->getDisplay());

        $this->setInputs(['test answer']);
        $this->call('test:interact');
        $this->assertHasSubString('The test question answer is : test answer', $this->getDisplay());
    }

    /**
     * Test composer.json file not found exception.
     *
     * @throws \ReflectionException
     */
    public function testComposerFileNotFoundException()
    {
        $file = $this->vfs()->url() . '/composer/composer.json';

        $this->setExpectedException(
            \RuntimeException::class,
            sprintf('The composer.json file could not be found in [%s]!', $file)
        );

        (new Application($this->vfs()->url() . '/composer/'))->getRootNamespace();
    }

    /**
     * Test parse composer.json failed exception.
     *
     * @throws \ReflectionException
     */
    public function testFailedToParseComposerFileException()
    {
        $file = $this->vfs()->url() . '/composer/non-json/composer.json';

        $this->setExpectedException(
            \RuntimeException::class,
            sprintf('Failed to parse the content of [%s], please check whether the file content is correct!', $file)
        );

        (new Application($this->vfs()->url() . '/composer/non-json/'))->getRootNamespace();
    }

    /**
     * Test unable to get root namespace from composer.json exception.
     *
     * @throws \ReflectionException
     */
    public function testUnableGetRootNamespaceFromComposerFileException()
    {
        $file = $this->vfs()->url() . '/composer/non-namespace/composer.json';

        $this->setExpectedException(
            \RuntimeException::class,
            sprintf('Unable to get root namespace from [%s], please check whether the file content is correct!', $file)
        );

        (new Application($this->vfs()->url() . '/composer/non-namespace/'))->getRootNamespace();
    }
}
