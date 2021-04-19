<?php /** @noinspection PhpIncludeInspection */

namespace Foris\Easy\Console\Tests;

use Foris\Easy\Console\Demo\Application;
use Foris\Easy\Console\Test\ConsoleTestSuite;
use org\bovigo\vfs\vfsStream;

/**
 * Class TestCase
 *
 * @method expectException($class)
 * @method expectExceptionMessage($message)
 * @method setExpectedException($class, $message = "", $code = null)
 * @method assertStringContainsString(string $needle, string $haystack, string $message = '')
 * @method assertStringContainsStringIgnoringCase(string $needle, string $haystack, string $message = '')
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    use ConsoleTestSuite;

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
    protected function setUp()
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

    /**
     * Assert a given string is a sub-string of another string.
     *
     * @param string $needle
     * @param string $haystack
     * @param string $message
     */
    protected function assertHasSubString($needle, $haystack, $message = '')
    {
        if (method_exists($this, 'assertStringContainsString')) {
            $this->assertStringContainsString($needle, $haystack, $message);
            return ;
        }

        $this->assertTrue(mb_strpos($haystack, $needle) !== false);
    }

    /**
     * Assert a given string is a sub-string of another string.
     *
     * @param string $needle
     * @param string $haystack
     * @param string $message
     */
    protected function assertHasSubStringIgnoringCase($needle, $haystack, $message = '')
    {
        if (method_exists($this, 'assertStringContainsStringIgnoringCase')) {
            $this->assertStringContainsStringIgnoringCase($needle, $haystack, $message);
            return ;
        }

        $this->assertTrue(mb_stripos($haystack, $needle) !== false);
    }
}
