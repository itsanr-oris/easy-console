<?php

namespace Foris\Easy\Console\Tests;

use Foris\Easy\Console\Tests\Mock\InteractsWithIOCommand;
use Foris\Easy\Support\Collection;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Tester\TesterTrait;

/**
 * Class InteractsWithIOTest
 */
class InteractsWithIOTest extends TestCase
{
    use TesterTrait {
        initOutput as traitInitOutput;
        setInputs as traitSetInputs;
    }

    /**
     * ArrayInput instance.
     *
     * @var ArrayInput
     */
    protected $input;

    /**
     * Command instance
     *
     * @var InteractsWithIOCommand
     */
    protected $command;

    /**
     * Initializes the input property.
     *
     * @return ArrayInput
     */
    protected function initInput()
    {
        $this->input = new ArrayInput(['test-argument' => 'test-argument-value', '--test-option' => true]);
        $this->input->setStream(self::createStream($this->inputs));
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
        $this->traitInitOutput($options);
        return $this->getOutput();
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
    protected function setInputs(array $inputs)
    {
        $this->traitSetInputs($inputs);
        $this->command()->run($this->initInput(), $this->initOutput());
        return $this;
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
        $this->assertStringContainsString($expected, $this->getDisplay());
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
        $this->assertStringContainsString($expected, $this->getDisplay());
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
        $expected = 'anticipate question [choice-1]:';

        $this->command()->anticipate('anticipate question', ['choice-1', 'choice-2'], 'choice-1');
        $this->assertStringContainsString($expected, $this->getDisplay());

        $this->setInputs(['choice-1']);
        $callable = function () {
            return ['choice-1', 'choice-2'];
        };
        $this->command()->anticipate('anticipate question', $callable, 'choice-1');
        $this->assertStringContainsString($expected, $this->getDisplay());
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
        $this->assertStringContainsString('choice question [choice-1]:', $display);
        $this->assertStringContainsString('[0] choice-1', $display);
        $this->assertStringContainsString('[1] choice-2', $display);
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
        $this->assertStringContainsString('+---------+---------+', $display);
        $this->assertStringContainsString('| title-1 | title-2 |', $display);
        $this->assertStringContainsString('| row-1   | row-2   |', $display);
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
            $output->shouldReceive('newLine')->andReturnTrue();
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
        $this->assertStringContainsString('success write [info] message: [This is an info message.]', $this->getDisplay());
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
        $this->assertStringContainsString('success write [comment] message: [This is a comment message.]', $this->getDisplay());
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
        $this->assertStringContainsString('success write [question] message: [This is a question message.]', $this->getDisplay());
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
        $this->assertStringContainsString('success write [error] message: [This is a error message.]', $this->getDisplay());
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
        $this->assertStringContainsString('success write [warning] message: [This is a warning message.]', $this->getDisplay());
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
        $this->assertStringContainsString('success write [alert] message: [This is a alert message.]', $this->getDisplay());
    }
}
