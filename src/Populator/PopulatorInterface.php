<?php

declare(strict_types=1);

namespace araise\SearchBundle\Populator;

interface PopulatorInterface
{
    public function index(object $entity): void;

    public function remove(object $entity, mixed $id = null): void;

    public function populate(?PopulateOutputInterface $output, ?string $entityClass): void;

    public function disableEntityListener(bool $disable);

    public function resetVisited(): void;
}
