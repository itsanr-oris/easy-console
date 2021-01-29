<?php /** @noinspection PhpParamsInspection */
/** @noinspection PhpUndefinedClassInspection */

/** @noinspection PhpMethodParametersCountMismatchInspection */

namespace Foris\Easy\Console\Tests;

use Foris\Easy\Support\Collection;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class InteractsWithIOTest
 */
class InteractsWithIOTest extends TestCase
{
    /**
     * ArrayInput instance.
     *
     * @var ArrayInput
     */
    protected $input;

    /**
     * ConsoleOutput instance.
     *
     * @var ConsoleOutput
     */
    protected $output;

    /**
     * Command instance
     *
     * @var InteractsWithIOCommand
     */
    protected $command;

    /**
     * Create input and output streams
     *
     * @param array $inputs
     * @return resource
     */
    protected function createStream(array $inputs)
    {
        $stream = fopen('php://memory', 'r+', false);

        foreach ($inputs as $input) {
            fwrite($stream, $input . \PHP_EOL);
        }

        rewind($stream);

        return $stream;
    }

    /**
     * Initializes the input property.
     *
     * @param array $inputs
     * @return ArrayInput
     */
    protected function initInput($inputs = [])
    {
        $this->input = new ArrayInput(['test-argument' => 'test-argument-value', '--test-option' => true]);
        $this->input->setStream($this->createStream($inputs));
        return $this->input;
    }

    /**
     * Initializes the output property.
     *
     * @param array $options
     * @return \Symfony\Component\Console\Output\OutputInterface
     */
    protected function initOutput($options = [])
    {
        $this->output = new StreamOutput(fopen('php://memory', 'w', false));
        if (isset($options['decorated'])) {
            $this->output->setDecorated($options['decorated']);
        }
        if (isset($options['verbosity'])) {
            $this->output->setVerbosity($options['verbosity']);
        }
        return $this->output;
    }

    /**
     * Get InteractsWithIO mock instance
     *
     * @return InteractsWithIOCommand
     * @throws \Exception
     */
    protected function command()
    {
        if (empty($this->command)) {
            $this->command = new InteractsWithIOCommand();
            $this->command->run($this->initInput(), $this->initOutput());
        }

        return $this->command;
    }

    /**
     * Sets the user inputs.
     *
     * @param array $inputs
     * @return InteractsWithIOTest
     * @throws \Exception
     */
    protected function setInputs($inputs = [])
    {
        $this->command()->run($this->initInput($inputs), $this->initOutput());
        return $this;
    }

    /**
     * Gets the display returned by the last execution of the command or application.
     *
     * @param bool $normalize
     * @return string The display
     */
    public function getDisplay($normalize = false)
    {
        if (null === $this->output) {
            throw new \RuntimeException('Output not initialized, did you execute the command before requesting the display?');
        }

        rewind($this->output->getStream());

        $display = stream_get_contents($this->output->getStream());

        if ($normalize) {
            $display = str_replace(\PHP_EOL, "\n", $display);
        }

        return $display;
    }

    /**
     * Test InteractsWithIO::hasArgument()
     *
     * @throws \Exception
     */
    public function testHasArgument()
    {
        $this->assertTrue($this->command()->hasArgument('test-argument'));
    }

    /**
     * Test InteractsWithIO::argument()
     * Test InteractsWithIO::arguments()
     *
     * @throws \Exception
     */
    public function testArgument()
    {
        $this->assertEquals('test-argument-value', $this->command()->argument('test-argument'));
        $this->assertEquals(['test-argument' => 'test-argument-value'], $this->command()->arguments());
    }

    /**
     * Test InteractsWithIO::hasOption()
     *
     * @throws \Exception
     */
    public function testHasOption()
    {
        $this->assertTrue($this->command()->hasOption('test-option'));
    }

    /**
     * Test InteractsWithIO::option()
     * Test InteractsWithIO::options()
     *
     * @throws \Exception
     */
    public function testOption()
    {
        $this->assertTrue($this->command()->option('test-option'));
        $this->assertEquals(['test-option' => true], $this->command()->options());
    }

    /**
     * Test InteractsWithIO::confirm()
     *
     * @throws \Exception
     */
    public function testConfirm()
    {
        $this->setInputs(['yes']);
        $expected = 'confirm question (yes/no) [yes]:';

        $this->command()->confirm('confirm question', 'default');
        $this->assertHasSubString($expected, $this->getDisplay());
    }

    /**
     * Test InteractsWithIO::ask()
     *
     * @throws \Exception
     */
    public function testAsk()
    {
        $this->setInputs(['yes']);
        $expected = 'ask question [default]:';

        $this->command()->ask('ask question', 'default');
        $this->assertHasSubString($expected, $this->getDisplay());
    }

    /**
     * Test InteractsWithIO::anticipate()
     * Test InteractsWithIO::askWithCompletion()
     *
     * @throws \Exception
     */
    public function testAnticipate()
    {
        $this->setInputs(['choice-1']);
        $question = 'anticipate question';
        $expected = 'anticipate question [choice-1]:';

        $this->command()->anticipate($question, ['choice-1', 'choice-2'], 'choice-1');
        $this->assertHasSubString($expected, $this->getDisplay());

        $this->setInputs(['choice-1']);
        $callable = function () {
            return ['choice-1', 'choice-2'];
        };

        if (method_exists(new Question($question), 'setAutocompleterCallback')) {
            $this->command()->anticipate('anticipate question', $callable, 'choice-1');
            $this->assertHasSubString($expected, $this->getDisplay());
        } else {
            if (class_exists( 'PHPUnit\Runner\Version' )) {
                $this->expectException(\RuntimeException::class);
                $this->expectExceptionMessage('Parameter [choices] only accepts array type parameters!');
            } else {
                $this->setExpectedException(\RuntimeException::class, 'Parameter [choices] only accepts array type parameters!');
            }
        }

        $this->command()->anticipate('anticipate question', $callable, 'choice-1');
        $this->assertHasSubString($expected, $this->getDisplay());
    }

    /**
     * Test InteractsWithIO::secret()
     *
     * @throws \Exception
     */
    public function testSecret()
    {
        $question = new Question('secret question');
        $question->setHidden(true)->setHiddenFallback(true);

        $mockOutput = \Mockery::mock(SymfonyStyle::class);
        $mockOutput->shouldReceive('askQuestion')->andReturn('answer');

        $this->command()->setOutput($mockOutput);
        $this->assertEquals('answer', $this->command()->secret('secret question', true));
    }

    /**
     * Test InteractsWithIO::choice()
     * @throws \Exception
     */
    public function testChoice()
    {
        $this->setInputs([1]);
        $choices = ['choice-1', 'choice-2'];
        $this->command()->choice('choice question', $choices, 'choice-1');

        $display = $this->getDisplay();
        $this->assertHasSubString('choice question [choice-1]:', $display);
        $this->assertHasSubString('[0] choice-1', $display);
        $this->assertHasSubString('[1] choice-2', $display);
    }

    /**
     * Test InteractsWithIO::table()
     *
     * @throws \Exception
     */
    public function testTable()
    {
        $this->command()->table(
            ['title-1', 'title-2'], new Collection([['row-1', 'row-2']]), 'default', ['default']
        );

        $display = $this->getDisplay();
        $this->assertHasSubString('+---------+---------+', $display);
        $this->assertHasSubString('| title-1 | title-2 |', $display);
        $this->assertHasSubString('| row-1   | row-2   |', $display);
    }

    /**
     * Init write line output
     *
     * @param     $message
     * @param     $style
     * @param int $verbosity
     * @throws \Exception
     */
    protected function initWriteLineOutput($message, $style, $verbosity = OutputInterface::VERBOSITY_NORMAL)
    {
        $styleMessage = "<$style>$message</$style>";

        $output = \Mockery::mock(SymfonyStyle::class);

        if ($style != 'alert') {
            $output->shouldReceive('writeln')
                ->with($styleMessage, $verbosity)
                ->andReturnUsing(function () use ($message, $style, $verbosity) {
                    $this->initOutput()->writeln("success write [$style] message: [$message]", $verbosity);
                });
        } else {
            $output->shouldReceive('writeln')
                ->withAnyArgs()
                ->andReturnUsing(function () use ($message, $style, $verbosity) {
                    $this->initOutput()->writeln("success write [$style] message: [$message]", $verbosity);
                });
            $output->shouldReceive('newLine')->andReturn(true);
        }

        $output->shouldReceive('getFormatter')->andReturn($this->command()->getOutput()->getFormatter());
        $this->command()->setOutput($output);
    }

    /**
     * Test InteractsWithIO::info()
     *
     * @throws \Exception
     */
    public function testInfo()
    {
        $this->initWriteLineOutput('This is an info message.', 'info');
        $this->command()->info('This is an info message.');
        $this->assertHasSubString('success write [info] message: [This is an info message.]', $this->getDisplay());
    }

    /**
     * Test InteractsWithIO::comment()
     *
     * @throws \Exception
     */
    public function testComment()
    {
        $this->initWriteLineOutput('This is a comment message.', 'comment');
        $this->command()->comment('This is a comment message.');
        $this->assertHasSubString('success write [comment] message: [This is a comment message.]', $this->getDisplay());
    }

    /**
     * Test InteractsWithIO::question()
     *
     * @throws \Exception
     */
    public function testQuestion()
    {
        $this->initWriteLineOutput('This is a question message.', 'question');
        $this->command()->question('This is a question message.');
        $this->assertHasSubString('success write [question] message: [This is a question message.]', $this->getDisplay());
    }

    /**
     * Test InteractsWithIO::question()
     *
     * @throws \Exception
     */
    public function testError()
    {
        $this->initWriteLineOutput('This is a error message.', 'error');
        $this->command()->error('This is a error message.');
        $this->assertHasSubString('success write [error] message: [This is a error message.]', $this->getDisplay());
    }

    /**
     * Test InteractsWithIO::question()
     *
     * @throws \Exception
     */
    public function testWarning()
    {
        $this->initWriteLineOutput('This is a warning message.', 'warning');
        $this->command()->warn('This is a warning message.');
        $this->assertHasSubString('success write [warning] message: [This is a warning message.]', $this->getDisplay());
    }

    /**
     * Test InteractsWithIO::alert()
     *
     * @throws \Exception
     */
    public function testAlert()
    {
        $this->initWriteLineOutput('This is a alert message.', 'alert');
        $this->command()->alert('This is a alert message.');
        $this->assertHasSubString('success write [alert] message: [This is a alert message.]', $this->getDisplay());
    }
}
