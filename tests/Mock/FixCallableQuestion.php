<?php

namespace Foris\Easy\Console\Tests\Mock;

use Symfony\Component\Console\Question\Question;

/**
 * Class FixCallableQuestion
 */
class FixCallableQuestion extends Question
{
    /**
     * Sets the callback function used for the autocompleter.
     *
     * The callback is passed the user input as argument and should return an iterable of corresponding suggestions.
     *
     * @param callable $callback
     * @return $this
     */
    public function setAutocompleterCallback(callable $callback)
    {
        $this->setAutocompleterValues($callback());
        return $this;
    }
}
