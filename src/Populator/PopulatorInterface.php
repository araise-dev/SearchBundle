<?php

declare(strict_types=1);

namespace araise\SearchBundle\Populator;

interface PopulatorInterface
{
    public function index(object $entity);

    public function remove(object $entity);

    public function populate(?PopulateOutputInterface $output, ?string $entityClass): void;

    public function disableEntityListener(bool $disable);

    public function resetVisited(): void;
}
