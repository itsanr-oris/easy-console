<?php

namespace Foris\Easy\Console\Tests;

use Foris\Easy\Console\Commands\Command;
use Foris\Easy\Console\Commands\GenerateCommand;
use Foris\Easy\Support\Arr;
use Foris\Easy\Support\Filesystem;

/**
 * Class MakeCommandTest
 */
class MakeCommandTest extends TestCase
{
    /**
     * Get expected generate file content
     *
     * @param        $class
     * @param string $type
     * @return mixed|string
     * @throws \Foris\Easy\Support\Exceptions\FileNotFountException
     */
    protected function getExpectedFileContent($class, $type = 'command')
    {
        if (empty($class)) {
            return '';
        }

        $namespace = 'Project\Console\Commands';
        if (strrpos($class, '/') !== false) {
            $segments = explode('/', $class);
            $class = end($segments);

            $namespaceSegments = Arr::except($segments, [count($segments) - 1]);
            $namespace = $namespace . '\\' . implode('\\', $namespaceSegments);
        }

        $file = $type == 'generate-command' ? 'DummyGenerateCommand.stub' : 'DummyCommand.stub';
        $stub = Filesystem::get( __DIR__ . '/../src/Stubs/' . $file);

        $parentClass = $type == 'generate-command' ? GenerateCommand::class : Command::class;

        return str_replace(
            ['DummyNamespace', 'DummyClass', 'DummyParentClass', 'dummy:command'],
            [$namespace, $class, $parentClass, 'command:name'],
            $stub
        );
    }

    /**
     * Test command "make:command"
     *
     * @throws \Exception
     */
    public function testMakeCommand()
    {
        $this->app()->call('make:command', ['name' => 'Test/Command']);

        $file = $this->vfs()->url() . '/src/Console/Commands/Test/Command.php';
        $this->assertTrue(file_exists($file));
        $this->assertEquals($this->getExpectedFileContent('Test/Command'), Filesystem::get($file));

        $this->app()->call('make:command', ['name' => 'Test/Command', '--type' => 'generate-command']);
        $this->assertEquals($this->getExpectedFileContent('Test/Command'), Filesystem::get($file));

        $this->app()->call('make:command', ['name' => 'Test/Command', '--type' => 'generate-command', '--force' => true]);
        $this->assertEquals($this->getExpectedFileContent('Test/Command', 'generate-command'), Filesystem::get($file));
    }
}
