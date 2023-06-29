<?php

declare(strict_types=1);

namespace araise\SearchBundle\Populator;

use araise\CoreBundle\Manager\FormatterManager;
use araise\SearchBundle\Exception\ClassNotDoctrineMappedException;
use araise\SearchBundle\Exception\ClassNotIndexedEntityException;
use araise\SearchBundle\Manager\IndexManager;
use araise\SearchBundle\Repository\CustomSearchPopulateQueryBuilderInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Logging\Middleware;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\NullLogger;

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

    public function populate(?PopulateOutputInterface $output = null, ?string $entityClass = null): void
    {
        $this->entityManager->getConnection()->getConfiguration()->setMiddlewares([new Middleware(new NullLogger())]);

        if ($this->disableEntityListener) {
            return;
        }
        if ($output) {
            $this->output = $output;
        }

        $entities = $this->indexManager->getIndexedEntities();

        // for example disable unwanted EventListeners
        $this->prePopulate();

        // Flush index
        $this->output->log('Flushing index table');
        $this->indexManager->flush();

        if ($entityClass) {
            $entityExists = $this->entityManager->getMetadataFactory()->isTransient($entityClass);
            if ($entityExists) {
                throw new ClassNotDoctrineMappedException($entityClass);
            }

            if ($entityClass && ! \in_array($entityClass, $entities, true)) {
                throw new ClassNotIndexedEntityException($entityClass);
            }
        }

        $this->output->log(sprintf('Index %s entites', count($entities)));
        foreach ($entities as $entityName) {
            if ($entityClass && $entityName !== str_replace('\\\\', '\\', $entityClass)) {
                continue;
            }
            $this->indexEntity($entityName);
        }
    }

    public function remove(object $entity): void
    {
        if ($this->entityWasRemoved($entity)) {
            return;
        }

        if ($this->disableEntityListener) {
            return;
        }

        $entityName = ClassUtils::getClass($entity);
        if (! $this->indexManager->hasEntityIndexes($entityName)) {
            return;
        }
        $classes = $this->getClassTree($entityName);
        foreach ($classes as $class) {
            if (! $this->canBeIndexed($class)) {
                continue;
            }
            $idMethod = $this->indexManager->getIdMethod($entityName);
            $this->delete((string) $entity->{$idMethod}(), $class);
        }
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

    protected function canBeIndexed(string $class): bool
    {
        if (! $this->entityManager->getMetadataFactory()->hasMetadataFor($class)) {
            return false;
        }
        $metadata = $this->entityManager->getClassMetadata($class);
        if (! $this->indexManager->hasEntityIndexes($class) || $metadata->isMappedSuperclass) {
            return false;
        }
        return true;
    }

    protected function getIndexEntityWorkingValues(string $entityName): array|false
    {
        $entityClass = new \ReflectionClass($entityName);
        if ($entityClass->isAbstract()) {
            return false;
        }

        $this->output->log('Indexing of entity ' . $entityName);

        // Get required meta information
        $indexes = $this->indexManager->getIndexesOfEntity($entityName);
        $idMethod = $this->indexManager->getIdMethod($entityName);

        $repository = $this->entityManager->getRepository($entityName);

        if ($repository instanceof CustomSearchPopulateQueryBuilderInterface) {
            $queryBuilder = $repository->getCustomSearchPopulateQueryBuilder();
        } else {
            // get clean QueryBuilder
            $queryBuilder = $this->entityManager->createQueryBuilder();
            $queryBuilder->from($entityName, 'e')->select('e');
        }

        $entities = array_map(static fn (mixed $entity) => [$entity], iterator_to_array($queryBuilder->getQuery()->toIterable()));
        if ($repository instanceof CustomSearchPopulateQueryBuilderInterface) {
            $entityCount = $repository->customSearchPopulateCount();
        } else {
            $entityCount = $this->entityManager->getRepository($entityName)->count([]);
        }

        $this->output->progressStart($entityCount * count($indexes));

        return [$entities, $idMethod, $indexes];
    }

    abstract protected function indexEntity($entityName);

    protected function prePopulate()
    {
    }

    protected function bulkInsert(array $insertSqlParts, array $insertData)
    {
        $connection = $this->entityManager->getConnection();
        $bulkInsertStatetment = $connection->prepare('INSERT INTO araise_search_index (foreign_id, model, grp, content) VALUES ' . implode(',', $insertSqlParts));
        $counter = 0;
        foreach ($insertData as $data) {
            $counter++;
            $bulkInsertStatetment->bindValue($counter, $data);
        }
        $bulkInsertStatetment->executeStatement();
    }

    protected function update(string $id, string $content)
    {
        $connection = $this->entityManager->getConnection();
        $updateStatement = $connection->prepare('UPDATE araise_search_index SET content=? WHERE id=?');
        $updateStatement->bindValue(1, $content);
        $updateStatement->bindValue(2, $id);
        $updateStatement->executeStatement();
    }

    protected function delete(string $foreignId, string $model)
    {
        $connection = $this->entityManager->getConnection();
        $updateStatement = $connection->prepare('DELETE FROM araise_search_index WHERE foreign_id=? and model=?');
        $updateStatement->bindValue(1, $foreignId);
        $updateStatement->bindValue(2, $model);
        $updateStatement->executeStatement();
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
