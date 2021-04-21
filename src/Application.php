<?php /** @noinspection PhpIncludeInspection */
/** @noinspection PhpUndefinedClassInspection */

namespace Foris\Easy\Console;

use Foris\Easy\Console\Commands\MakeCommand;
use Foris\Easy\Support\Arr;
use Foris\Easy\Support\Filesystem;
use Foris\Easy\Support\Str;
use ReflectionClass;
use RuntimeException;
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
     * User interactive inputs.
     *
     * @var array
     */
    protected $inputs = [];

    /**
     * Determined whether to enable user interaction.
     *
     * @var bool
     */
    protected $interactive = false;

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
     * @param null   $rootPath
     * @param string $name
     * @param string $version
     */
    public function __construct($rootPath = null, $name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);
        $this->bootstrap($rootPath);
    }

    /**
     * Bootstrap the console application.
     *
     * @param $rootPath
     */
    protected function bootstrap($rootPath)
    {
        $this->setRootPath($rootPath);
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
     * Sets the app root path.
     *
     * @param $rootPath
     * @return $this
     */
    protected function setRootPath($rootPath)
    {
        $this->options['root_path'] = empty($rootPath) ? '' : Str::finish($rootPath, '/');
        return $this;
    }

    /**
     * Gets the app root path.
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

        return $this->options['root_path'] = Str::finish(Str::replaceLast('/src/', '/', $path), '/');
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
     * Gets the console app path
     *
     * @return string
     * @throws \ReflectionException
     */
    public function getConsolePath()
    {
        if (!empty($this->options['console_path'])) {
            return $this->options['console_path'];
        }

        $class = new ReflectionClass($this);
        return $this->options['console_path'] = Str::finish(dirname($class->getFileName()), '/');
    }

    /**
     * Gets the console app namespace.
     *
     * @return bool|mixed|string
     */
    public function getConsoleNamespace()
    {
        if (!empty($this->options['console_namespace'])) {
            return $this->options['console_namespace'];
        }

        $class = static::class;
        return $this->options['console_namespace'] = substr($class, 0, strrpos($class, '\\'));
    }

    /**
     * Gets the `composer.json` file content.
     *
     * @return array
     * @throws \ReflectionException
     */
    protected function getComposer()
    {
        $path = $this->getPath('composer.json');

        if (!file_exists($path)) {
            throw new RuntimeException(sprintf('The composer.json file could not be found in [%s]!', $path));
        }

        $composer = json_decode(file_get_contents($path), true);

        if (empty($composer)) {
            throw new RuntimeException(
                sprintf('Failed to parse the content of [%s], please check whether the file content is correct!', $path)
            );
        }

        return $composer;
    }

    /**
     * Gets the root namespace.
     *
     * @return int|string
     * @throws \ReflectionException
     */
    public function getRootNamespace()
    {
        if (!empty($this->options['root_namespace'])) {
            return $this->options['root_namespace'];
        }

        $composer = $this->getComposer();

        $len = -1;
        $rootNamespace = '';

        foreach ($composer['autoload']['psr-4'] as $namespace => $path) {
            if (strpos(static::class, $namespace) !== 0) {
                continue;
            }

            if ($len == -1 || $len > strlen($namespace)) {
                $len = strlen($namespace);
                $rootNamespace = $namespace;
            }
        }

        if (empty($rootNamespace)) {
            throw new RuntimeException(
                sprintf(
                    'Unable to get root namespace from [%s], please check whether the file content is correct!',
                    $this->getPath('composer.json')
                )
            );
        }

        return $this->options['root_namespace'] = $rootNamespace;
    }

    /**
     * Gets source code file path.
     *
     * @return mixed
     * @throws \ReflectionException
     */
    public function getSrcPath()
    {
        if (!empty($this->options['src_path'])) {
            return $this->options['src_path'];
        }

        $composer = $this->getComposer();
        return $this->options['src_path'] = Str::finish($this->getPath($composer['autoload']['psr-4'][$this->getRootNamespace()]), '/');
    }

    /**
     * Register commands in all given directories.
     *
     * @param  array|string $paths
     * @return void
     */
    public function load($paths)
    {
        $paths = array_unique(Arr::wrap($paths));

        $paths = array_filter($paths, function ($path) {
            return is_dir($path);
        });

        array_walk($paths, function ($path) { $this->discover($path);});
    }

    /**
     * Discover and register commands in the given directory.
     *
     * @param $path
     * @throws \ReflectionException
     */
    protected function discover($path)
    {
        $srcPath = $this->getSrcPath();
        $rootNamespace = $this->getRootNamespace();

        foreach (Filesystem::scanFiles($path) as $item) {
            $class = ucfirst(str_replace([$srcPath, '.php', '/'], [$rootNamespace, '', '\\'], $item));

            if (!class_exists($class)
                || !is_subclass_of($class, Command::class)
                || !(new ReflectionClass($class))->isInstantiable()) {
                continue;
            }

            $this->add(new $class());
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

        if ($this->isInteractive()) {
            $input->setStream($this->getInputStream());
        }

        return $this->run(
            $input, $this->lastOutput = $outputBuffer ?: new BufferedOutput
        );
    }


    /**
     * Sets the user interactive inputs.
     *
     * @param array $inputs
     * @return $this
     */
    public function setInputs($inputs = [])
    {
        $this->inputs = $inputs;
        return $this;
    }

    /**
     * Enable user interactive.
     *
     * @param bool $interactive
     * @return $this
     */
    public function setInteractive($interactive = true)
    {
        $this->interactive = $interactive;
        putenv('SHELL_INTERACTIVE=true');
        return $this;
    }

    /**
     * Is this input means interactive?
     *
     * @return bool
     */
    public function isInteractive()
    {
        return $this->interactive;
    }

    /**
     * Gets the user interactive input stream.
     *
     * @return bool|resource
     */
    public function getInputStream()
    {
        $stream = fopen('php://memory', 'r+', false);

        foreach ($this->inputs as $input) {
            fwrite($stream, $input . \PHP_EOL);
        }

        rewind($stream);

        return $stream;
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
