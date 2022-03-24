<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Populator;

use Doctrine\ORM\EntityManagerInterface;
use whatwedo\CoreBundle\Manager\FormatterManager;
use whatwedo\SearchBundle\Manager\IndexManager;

abstract class AbstractPopulator implements PopulatorInterface
{
    protected array $indexVisited = [];

    protected array $removeVisited = [];

    protected PopulateOutputInterface $output;

    protected bool $disableEntityListener = false;

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected IndexManager $indexManager,
        protected FormatterManager $formatterManager
    ) {
        $this->output = new NullPopulateOutput();
    }

    public function disableEntityListener(bool $disable)
    {
        $this->disableEntityListener = $disable;
    }

    public function resetVisited(): void
    {
        $this->removeVisited = [];
        $this->indexVisited = [];
    }

    protected function bulkInsert(array $insertSqlParts, array $insertData)
    {
        $connection = $this->entityManager->getConnection();
        $bulkInsertStatetment = $connection->prepare('INSERT INTO whatwedo_search_index (foreign_id, model, grp, content) VALUES ' . implode(',', $insertSqlParts));
        $bulkInsertStatetment->executeStatement($insertData);
    }

    protected function update(string $id, string $content)
    {
        $connection = $this->entityManager->getConnection();
        $updateStatement = $connection->prepare('UPDATE whatwedo_search_index SET content=? WHERE id=?');
        $updateStatement->executeStatement([$content, $id]);
    }

    protected function delete(string $foreignId, string $model)
    {
        $connection = $this->entityManager->getConnection();
        $updateStatement = $connection->prepare('DELETE FROM whatwedo_search_index WHERE foreign_id=? and model=?');
        $updateStatement->executeStatement([$foreignId, $model]);
    }

    protected function entityWasIndexed(object $entity): bool
    {
        $oid = spl_object_hash($entity);
        if (isset($this->indexVisited[$oid])) {
            return true;
        }
        $this->indexVisited[$oid] = true;

        return false;
    }

    protected function entityWasRemoved(object $entity): bool
    {
        $oid = spl_object_hash($entity);
        if (isset($this->removeVisited[$oid])) {
            return true;
        }
        $this->removeVisited[$oid] = true;

        return false;
    }

    protected function getClassTree($entityFqcn): array
    {
        $classes = class_parents($entityFqcn);
        array_unshift($classes, $entityFqcn);

        return $classes;
    }
}