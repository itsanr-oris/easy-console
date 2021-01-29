<?php /** @noinspection PhpUndefinedClassInspection */

namespace Foris\Easy\Console;

use Foris\Easy\Console\Commands\MakeCommand;
use Foris\Easy\Support\Arr;
use Foris\Easy\Support\Filesystem;
use Foris\Easy\Support\Str;
use ReflectionClass;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Application
 */
class Application extends SymfonyApplication
{
    /**
     * The output from the previous command.
     *
     * @var OutputInterface
     */
    protected $lastOutput;

    /**
     * Application options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Application constructor.
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        $name = isset($options['name']) ? $options['name'] : 'UNKNOWN';
        $version = isset($options['version']) ? $options['version'] : 'UNKNOWN';

        $this->bootstrap($options);
        parent::__construct($name, $version);
    }

    /**
     * Bootstrap the console application.
     *
     * @param array $options
     */
    protected function bootstrap($options = [])
    {
        $this->options = $options;

        $this->commands();
        $this->setAutoExit(false);
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->add(new MakeCommand());
    }

    /**
     * Set the root namespace.
     *
     * @param $namespace
     * @return $this
     */
    public function setRootNamespace($namespace)
    {
        $this->options['root_namespace'] = $namespace;
        return $this;
    }

    /**
     * Get the root namespace.
     *
     * @return string
     */
    public function getRootNamespace()
    {
        if (!empty($this->options['root_namespace'])) {
            return $this->options['root_namespace'];
        }

        $class = static::class;
        return $this->options['root_namespace'] = substr($class, 0, strrpos($class, '\\'));
    }

    /**
     * Set the app root path.
     *
     * @param $path
     * @return $this
     */
    public function setRootPath($path)
    {
        $this->options['root_path'] = $path;
        return $this;
    }

    /**
     * Get the app root path.
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getRootPath()
    {
        if (!empty($this->options['root_path'])) {
            return $this->options['root_path'];
        }

        $class = new ReflectionClass($this);
        $path = Str::finish(dirname($class->getFileName()), '/');

        return $this->options['root_path'] = Str::replaceLast('/src/', '/', $path);
    }

    /**
     * Get the destination class path.
     *
     * @param string $path
     * @return string
     * @throws \ReflectionException
     */
    public function getPath($path = '')
    {
        return Str::start($path, Str::finish($this->getRootPath(), '/'));
    }

    /**
     * Gets source code file path.
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getSrcPath()
    {
        return $this->getPath('src');
    }

    /**
     * Register all of the commands in the given directory.
     *
     * @param  array|string $paths
     * @return void
     * @throws \ReflectionException
     */
    public function load($paths)
    {
        $paths = array_unique(Arr::wrap($paths));

        $paths = array_filter($paths, function ($path) {
            return is_dir($path);
        });

        if (empty($paths)) {
            return;
        }

        $srcPath = $this->getSrcPath();
        $rootNamespace = $this->getRootNamespace();

        foreach ($paths as $path) {
            foreach (Filesystem::scanFiles($path) as $item) {
                $class = ucfirst(str_replace([$srcPath, '.php', '/'], [$rootNamespace, '', '\\'], $item));

                if (!class_exists($class) || !is_subclass_of($class, Command::class)) {
                    continue;
                }

                $reflect = new ReflectionClass($class);

                if (!$reflect->isInstantiable()) {
                    continue;
                }

                $this->add(new $class());
            }
        }
    }

    /**
     * Run an Artisan console command by name.
     *
     * @param  string $command
     * @param  array $parameters
     * @param  OutputInterface|null $outputBuffer
     * @return int
     *
     * @throws \Exception
     */
    public function call($command, array $parameters = [], $outputBuffer = null)
    {
        if (! $this->has($command)) {
            throw new CommandNotFoundException(sprintf('The command "%s" does not exist.', $command));
        }

        array_unshift($parameters, $command);
        $input = new ArrayInput($parameters);

        return $this->run(
            $input, $this->lastOutput = $outputBuffer ?: new BufferedOutput
        );
    }

    /**
     * Get the output for the last run command.
     *
     * @return string
     */
    public function output()
    {
        return $this->lastOutput && method_exists($this->lastOutput, 'fetch')
            ? $this->lastOutput->fetch()
            : '';
    }
}
