<?php /** @noinspection PhpIncludeInspection */

namespace Foris\Easy\Console\Tests;

use Foris\Easy\Console\Application;
use org\bovigo\vfs\vfsStream;

/**
 * Class TestCase
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * vfs instance
     *
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    protected $vfs = null;

    /**
     * Gets the application instance
     *
     * @param bool $newInstance
     * @return Application|mixed
     */
    protected function app($newInstance = false)
    {
        $this->initVfs();

        if ($newInstance || !$this->app instanceof Application) {
            $class = 'Project\Console\Application';
            return $this->app = new $class();
        }

        return $this->app;
    }

    /**
     * Get vfs instance
     *
     * @return \org\bovigo\vfs\vfsStreamDirectory
     */
    protected function vfs()
    {
        if (empty($this->vfs)) {
            $base = vfsStream::setup('project');
            $this->vfs = vfsStream::copyFromFileSystem(__DIR__ . '/vfs/project', $base);

            require_once $this->vfs->url() . '/src/Console/Application.php';
            require_once $this->vfs->url() . '/src/Console/Commands/AbstractCommand.php';
            require_once $this->vfs->url() . '/src/Console/Commands/HelloCommand.php';
            require_once $this->vfs->url() . '/src/Console/Commands/IllegalCommand.php';
            require_once $this->vfs->url() . '/src/Application.php';

            return $this->vfs;
        }

        return $this->vfs;
    }

    /**
     * Init vfs instance
     *
     * @return \org\bovigo\vfs\vfsStreamDirectory
     */
    protected function initVfs()
    {
        return empty($this->vfs) ? $this->vfs() : $this->vfs;
    }
}
