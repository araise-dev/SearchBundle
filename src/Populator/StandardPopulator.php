<?php

declare(strict_types=1);

namespace araise\SearchBundle\Populator;

use araise\SearchBundle\Entity\Index;
use araise\SearchBundle\Exception\MethodNotFoundException;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL;
use Doctrine\ORM\Mapping\MappingException;

class StandardPopulator extends AbstractPopulator
{
    /**
     * @throws MethodNotFoundException
     * @throws MappingException
     * @throws \ReflectionException
     * @throws DBAL\Exception
     */
    public function index(object $entity): void
    {
        if ($this->disableEntityListener) {
            return;
        }

        if ($entity instanceof Index) {
            return;
        }

        if ($this->entityWasIndexed($entity)) {
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

            $indexes = $this->indexManager->getIndexesOfEntity($class);
            $idMethod = $this->indexManager->getIdMethod($class);

            /** @var \araise\SearchBundle\Annotation\Index $index */
            foreach ($indexes as $field => $index) {
                $fieldMethod = $this->indexManager->getFieldAccessorMethod($class, $field);
                $formatter = $this->formatterManager->getFormatter($index->getFormatter());
                if (method_exists($formatter, 'processOptions')) {
                    $formatter->processOptions($index->getFormatterOptions());
                }
                $content = $formatter->getString($entity->{$fieldMethod}());
                if (! empty($content)) {
                    $entry = $this->entityManager->getRepository(Index::class)->findExisting($class, $field, $entity->{$idMethod}());
                    if (! $entry) {
                        $insertData = [];
                        $insertSqlParts = [];
                        $insertData[] = $entity->{$idMethod}();
                        $insertData[] = $class;
                        $insertData[] = $field;
                        $insertData[] = (string) $content;
                        $insertSqlParts[] = '(?,?,?,?)';

                        $this->bulkInsert($insertSqlParts, $insertData);
                    } else {
                        $this->update($entry->{$idMethod}(), $content);
                    }
                }
            }
        }
    }

    /**
     * Populate index of given entity.
     *
     * @throws DBAL\Exception
     * @throws MappingException
     * @throws MethodNotFoundException
     * @throws \ReflectionException
     */
    protected function indexEntity(string $entityName): void
    {
        [$entities, $idMethod, $indexes] = $this->getIndexEntityWorkingValues($entityName);

        $i = 0;
        $insertData = [];
        $insertSqlParts = [];

        foreach ($entities as $entity) {
            /** @var \araise\SearchBundle\Annotation\Index $index */
            foreach ($indexes as $field => $index) {
                $fieldMethod = $this->indexManager->getFieldAccessorMethod($entityName, $field);

                $formatter = $this->formatterManager->getFormatter($index->getFormatter());
                $formatter->processOptions($index->getFormatterOptions());
                $content = $formatter->getString($entity[0]->{$fieldMethod}());

                // Persist entry
                if (! empty($content)) {
                    $insertData[] = $entity[0]->{$idMethod}();
                    $insertData[] = $entityName;
                    $insertData[] = $field;
                    $insertData[] = (string) $content;
                    $insertSqlParts[] = '(?,?,?,?)';
                }

                // Update progress bar every 200 iterations
                // as well as gc
                if ($i % 200 === 0) {
                    if (count($insertData)) {
                        $this->bulkInsert($insertSqlParts, $insertData);
                    }
                    $insertSqlParts = [];
                    $insertData = [];

                    $this->output->setProgress($i);
                    $this->gc();
                }
                ++$i;
            }
        }

        if (count($insertData)) {
            $this->bulkInsert($insertSqlParts, $insertData);
        }

        $this->gc();

        $this->output->progressFinish();
    }

    /**
     * Clean up garbage.
     */
    protected function gc(): void
    {
        $this->entityManager->clear();
        gc_collect_cycles();
    }
}
