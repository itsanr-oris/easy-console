<?php /** @noinspection PhpIncludeInspection */

namespace Foris\Easy\Console\Tests;

use Foris\Easy\Console\Demo\Application;
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
     * @return Application|mixed
     */
    protected function app()
    {
        return $this->app;
    }

    /**
     * Gets the vfs instance
     *
     * @return \org\bovigo\vfs\vfsStreamDirectory
     */
    protected function vfs()
    {
        return $this->vfs;
    }

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->vfs = $this->initVfs();
        $this->app = new Application();
    }

    /**
     * Get vfs instance
     *
     * @return \org\bovigo\vfs\vfsStreamDirectory
     */
    protected function initVfs()
    {
        if (empty($this->vfs)) {
            $base = vfsStream::setup('demo-console');
            $this->vfs = vfsStream::copyFromFileSystem(__DIR__ . '/../demo', $base);
            require_once $this->vfs->url() . '/autoload.php';
        }

        return $this->vfs;
    }
}
