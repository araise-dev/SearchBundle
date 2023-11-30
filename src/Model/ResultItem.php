<?php

declare(strict_types=1);

namespace araise\SearchBundle\Model;

class ResultItem
{
    public function __construct(
        private readonly int $id,
        private readonly string $class,
        private readonly float $score,
        private $entity
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getScore(): float
    {
        return $this->score;
    }

    public function getEntity()
    {
        return $this->entity;
    }
}
