<?php /** @noinspection PhpUndefinedClassInspection */

namespace Foris\Easy\Console\Commands;

use Foris\Easy\Support\Filesystem;
use Foris\Easy\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class GenerateCommand
 */
abstract class GenerateCommand extends Command
{
    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'class';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the class'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(),[
            ['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the component already exists'],
        ]);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    abstract protected function getStub();

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->argument('name'));
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace;
    }

    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param  string $name
     * @return string
     * @throws \ReflectionException
     */
    protected function qualifyClass($name)
    {
        $name = ltrim($name, '\\/');

        $rootNamespace = $this->getRootNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        $name = str_replace('/', '\\', $name);

        return $this->qualifyClass(
            $this->getDefaultNamespace(trim($rootNamespace, '\\')).'\\'.$name
        );
    }

    /**
     * Build the class with the given name.
     *
     * @param $name
     * @return mixed
     * @throws \Foris\Easy\Support\Exceptions\FileNotFountException
     * @throws \ReflectionException
     */
    public function buildClass($name)
    {
        $stub = Filesystem::get($this->getStub());

        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param $stub
     * @param $name
     * @return $this
     * @throws \ReflectionException
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace(
            ['DummyNamespace', 'DummyRootNamespace'],
            [$this->getNamespace($name), $this->getRootNameSpace()],
            $stub
        );

        return $this;
    }

    /**
     * Get the full namespace for a given class, without the class name.
     *
     * @param $name
     * @return string
     */
    protected function getNamespace($name)
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param $stub
     * @param $name
     * @return mixed
     */
    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        return str_replace('DummyClass', $class, $stub);
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
        $name = $this->qualifyClass($this->getNameInput());

        $path = $this->getPath($name);

        // First we will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ((! $this->hasOption('force') ||
                ! $this->option('force')) &&
            $this->alreadyExists($this->getNameInput())) {
            $this->error($this->type.' already exists!');

            return false;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        Filesystem::put($path, $this->buildClass($name));

        $this->info($this->type.' created successfully.');

        return true;
    }

    /**
     * Gets the root namespace.
     *
     * @return string
     * @throws \ReflectionException
     */
    protected function getRootNamespace()
    {
        return $this->getApplication()->getRootNamespace();
    }

    /**
     * Gets source code file path.
     *
     * @return string
     * @throws \ReflectionException
     */
    protected function getSrcPath()
    {
        return $this->getApplication()->getSrcPath();
    }

    /**
     * Get the destination class path.
     *
     * @param  string $name
     * @return string
     * @throws \ReflectionException
     */
    protected function getPath($name)
    {
        $name = Str::replaceFirst(Str::finish($this->getRootNamespace(), '\\'), '', $name);
        return $this->getSrcPath() . str_replace('\\', '/', $name) . '.php';
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (!Filesystem::isDirectory(dirname($path))) {
            Filesystem::makeDirectory(dirname($path), 0755, true, true);
        }

        return $path;
    }

    /**
     * Determine if the class already exists.
     *
     * @param  string $rawName
     * @return bool
     * @throws \ReflectionException
     */
    protected function alreadyExists($rawName)
    {
        return Filesystem::exists($this->getPath($this->qualifyClass($rawName)));
    }
}
