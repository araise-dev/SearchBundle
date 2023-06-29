<?php

declare(strict_types=1);

namespace araise\SearchBundle\Tokenizer;

abstract class AbstractTokenizer implements TokenizerInterface
{
    public function getPriority(): int
    {
        return 0;
    }
}
