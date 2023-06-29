<?php

declare(strict_types=1);

namespace araise\SearchBundle\Filter;

interface FilterInterface
{
    public function setOptions(array $options): void;

    public function process(array $data): array;

    public function getPriority(): int;
}
