<?php

declare(strict_types=1);
/**
 * Copyright (c) 2016, whatwedo GmbH
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace araise\SearchBundle\Manager;

use araise\SearchBundle\Annotation\Index as AttributeIndex;
use araise\SearchBundle\Entity\Index as EntityIndex;
use araise\SearchBundle\Exception\MethodNotFoundException;
use araise\SearchBundle\Repository\IndexRepositoryInterface;
use Doctrine\Common\Annotations\Reader;
use Doctrine\DBAL;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;

class IndexManager
{
    protected array $config = [];

    protected array $entityFields = [];

    private array $annotationFields = [];

    public function __construct(
        protected ManagerRegistry $doctrine,
        private readonly Reader $annotationReader
    ) {
    }

    /**
     * Flush index table.
     * @throws DBAL\Exception
     */
    public function flush(): void
    {
        $connection = $this->getEntityManager()->getConnection();
        /** @var DBAL\Platforms\AbstractPlatform $dbPlatform */
        $dbPlatform = $connection->getDatabasePlatform();
        $tableName = $this->getEntityManager()->getClassMetadata(EntityIndex::class)->getTableName();
        $query = $dbPlatform->getTruncateTableSql($tableName);
        $connection->prepare($query)->executeStatement();
    }

    /**
     * Get indexes of given entity.
     * @throws \ReflectionException
     */
    public function getIndexesOfEntity(string $entityFqcn): array
    {
        if (! isset($this->entityFields[$entityFqcn])) {
            $fields = $this->getAnnotationFields($entityFqcn);
            $fields = array_merge($fields, $this->getAttrubuteFields($entityFqcn));

            // Check if entities exists
            if (isset($this->config['entities'])) {
                foreach ($this->config['entities'] as $entityConfig) {
                    if ($entityConfig['class'] === $entityFqcn) {
                        foreach ($entityConfig['fields'] as $fieldConfig) {
                            $annotation = new AttributeIndex();
                            if (isset($fieldConfig['formatter'])) {
                                $annotation->setFormatter($fieldConfig['formatter']);
                            }
                            $fields[$fieldConfig['name']] = $annotation;
                        }
                    }
                }
            }
            $this->entityFields[$entityFqcn] = $fields;
        }

        return $this->entityFields[$entityFqcn];
    }

    /**
     * Return true if there are at least one index in the
     * given entity.
     * @throws \ReflectionException
     */
    public function hasEntityIndexes(string $entity): bool
    {
        $indexes = $this->getIndexesOfEntity($entity);
        return \count($indexes) > 0;
    }

    /**
     * Get all entities with any defined index.
     * @throws \ReflectionException
     */
    public function getIndexedEntities(): array
    {
        $tables = [];
        $metaTables = $this->getEntityManager()->getMetadataFactory()->getAllMetadata();
        /** @var ClassMetadata $metaTable */
        foreach ($metaTables as $metaTable) {
            $entity = $metaTable->getName();
            $classMeta = $this->getEntityManager()->getClassMetadata($entity);
            if (property_exists($classMeta, 'isMappedSuperclass') && $classMeta->isMappedSuperclass === false && $this->hasEntityIndexes($entity)) {
                $tables[] = $entity;
            }
        }

        return $tables;
    }

    /**
     * Get id method.
     * @throws MethodNotFoundException|MappingException
     */
    public function getIdMethod(string $entityName): string
    {
        $field = $this->getEntityManager()->getClassMetadata($entityName)->getSingleIdentifierFieldName();

        return $this->getFieldAccessorMethod($entityName, $field);
    }

    /**
     * Get field accessor method.
     *
     * @throws MethodNotFoundException
     */
    public function getFieldAccessorMethod(string $entityName, string $field): string
    {
        $prefixes = [
            'get',
            'is',
            'has',
        ];
        if (method_exists($entityName, $field)) {
            return $field;
        }
        foreach ($prefixes as $prefix) {
            $method = $prefix.ucfirst($field);
            if (method_exists($entityName, $method)) {
                return $method;
            }
        }
        throw new MethodNotFoundException('Accessor method of field '.$field.' of entity '.$entityName.' not found');
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function getRepository(): IndexRepositoryInterface
    {
        /** @var IndexRepositoryInterface $repository */
        $repository = $this->doctrine->getRepository(EntityIndex::class);
        return $repository;
    }

    protected function getEntityManager(): EntityManager
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->doctrine->getManager();
        return $entityManager;
    }

    /**
     * @throws \ReflectionException
     */
    protected function getAnnotationFields(string $entityFqcn): array
    {
        if (! isset($this->annotationFields[$entityFqcn])) {
            $this->annotationFields[$entityFqcn] = [];
            $reflection = new \ReflectionClass($entityFqcn);
            foreach ($reflection->getProperties() as $property) {
                $annotation = $this->annotationReader->getPropertyAnnotation($property, AttributeIndex::class);
                if ($annotation !== null) {
                    $this->annotationFields[$entityFqcn][$property->getName()] = $annotation;
                }
            }
            foreach ($reflection->getMethods() as $method) {
                $annotation = $this->annotationReader->getMethodAnnotation($method, AttributeIndex::class);
                if ($annotation !== null) {
                    $this->annotationFields[$entityFqcn][$method->getName()] = $annotation;
                }
            }
        }

        return $this->annotationFields[$entityFqcn];
    }

    /**
     * @throws \ReflectionException
     */
    protected function getAttrubuteFields(string $entity): array
    {
        $fields = [];
        $reflection = new \ReflectionClass($entity);
        foreach ($reflection->getMethods() as $reflectionMethod) {
            $methodAttributes = $reflectionMethod->getAttributes();
            foreach ($methodAttributes as $attribute) {
                if ($attribute->getName() === AttributeIndex::class) {
                    $fields[$reflectionMethod->getName()] = $attribute->newInstance();
                }
            }
        }
        foreach ($reflection->getProperties() as $reflectionProperty) {
            $propertyAttributes = $reflectionProperty->getAttributes();
            foreach ($propertyAttributes as $attribute) {
                if ($attribute->getName() === AttributeIndex::class) {
                    $fields[$reflectionProperty->getName()] = $attribute->newInstance();
                }
            }
        }

        return $fields;
    }
}
