<?php

declare(strict_types=1);

namespace araise\SearchBundle\Tokenizer;

interface TokenizerInterface
{
    public function tokenize(string $data): array;

    public function getPriority(): int;
}
