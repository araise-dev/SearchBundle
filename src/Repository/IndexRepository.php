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

namespace araise\SearchBundle\Repository;

use araise\SearchBundle\Annotation\Searchable;
use araise\SearchBundle\Entity\Index;
use araise\SearchBundle\Entity\PostSearchInterface;
use araise\SearchBundle\Entity\PreSearchInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Persistence\ManagerRegistry;

class IndexRepository extends ServiceEntityRepository
{
    public function __construct(
        private bool $asteriskSearchEnabled,
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, Index::class);
    }

    public function search($query, $entity = null, $group = null): array
    {
        $query = $this->queryEscape($query);

        $qb = $this->createQueryBuilder('i')
            ->select('i.foreignId')
            ->addSelect('MATCH_AGAINST(i.content, :query) AS _matchQuote')
            ->setParameter('query', preg_replace('/%+/', ' ', $query))
        ;

        if (str_contains($query, '%')) {
            $qb->where('i.content LIKE :queryWildcard')
                ->setParameter('queryWildcard', $query);
        } else {
            $qb->where('MATCH_AGAINST(i.content, :query) > :minScore')
                ->orWhere('i.content LIKE :queryWildcard')
                ->setParameter('queryWildcard', '%'.$query.'%')
                ->setParameter('minScore', 0);
        }

        $qb->groupBy('i.foreignId')
            ->addGroupBy('_matchQuote')
            ->addOrderBy('_matchQuote', 'DESC');

        if ($entity) {
            $qb->andWhere('i.model = :entity')
                ->setParameter('entity', $entity);
        }

        if ($group) {
            $qb->andWhere('i.group = :group')
                ->setParameter('group', $group);
        }

        if ($entity) {
            // preSearch
            $reflection = new \ReflectionClass($entity);
            $annotationReader = new AnnotationReader();

            /** @var Searchable $searchableAnnotations */
            $searchableAnnotations = $annotationReader->getClassAnnotation($reflection, Searchable::class);

            if ($searchableAnnotations) {
                $class = $searchableAnnotations->getPreSearch();
                if ($class && class_exists($class)) {
                    $reflection = new \ReflectionClass($class);
                    if ($reflection->implementsInterface(PreSearchInterface::class)) {
                        (new $class())->preSearch($qb, $query, $entity, $group);
                    }
                }
            }
        }

        $result = $qb->getQuery()->getScalarResult();

        if ($entity) {
            // postSearch
            if ($searchableAnnotations) {
                $class = $searchableAnnotations->getPostSearch();
                if ($class && class_exists($class)) {
                    $reflection = new \ReflectionClass($class);
                    if ($reflection->implementsInterface(PostSearchInterface::class)) {
                        $result = (new $class())->postSearch($result, $query, $entity, $group);
                    }
                }
            }
        }

        $ids = [];
        foreach ($result as $row) {
            $ids[] = $row['foreignId'];
        }

        return $ids;
    }

    public function searchEntities($query, array $entities = [], array $groups = []): array
    {
        $query = $this->queryEscape($query);

        $qb = $this->createQueryBuilder('i');
        $qb->select('i.foreignId as id');
        $qb->addSelect('MATCH_AGAINST(i.content, :query) AS _matchQuote');
        $qb->addSelect('i.model');

        if (str_contains($query, '%')) {
            $qb->where('i.content LIKE :queryWildcard');
            $qb->setParameter('query', preg_replace('/%+/', ' ', $query));
            $qb->setParameter('queryWildcard', $query);
        } else {
            $qb->where('MATCH_AGAINST(i.content, :query) > :minScore');
            $qb->setParameter('minScore', 0);
            $qb->setParameter('query', sprintf('*%s*', $query));
        }

        $qb->groupBy('i.foreignId');
        $qb->addGroupBy('_matchQuote');
        $qb->addGroupBy('i.model');
        $qb->addOrderBy('_matchQuote', 'DESC');

        $ors = $qb->expr()->orX();

        foreach ($entities as $key => $entity) {
            $ors->add($qb->expr()->eq('i.model', ':entity_'.$key));
            $qb->setParameter('entity_'.$key, $entity);
        }
        $qb->andWhere(
            $ors
        );

        foreach ($groups as $key => $group) {
            $qb->andWhere('i.group = :groupName_'.$key)
                ->setParameter(':groupName_'.$key, $group);
        }

        return $qb->getQuery()->getResult();
    }

    public function findExisting(string $entityFqcn, string $group, int $foreignId): ?Index
    {
        return $this->createQueryBuilder('i')
            ->where('i.model = :entity')
            ->andWhere('i.group = :group')
            ->andWhere('i.foreignId = :foreignId')
            ->setParameter('entity', $entityFqcn)
            ->setParameter('group', $group)
            ->setParameter('foreignId', $foreignId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function isAsteriskSearchEnabled(): bool
    {
        return $this->asteriskSearchEnabled;
    }

    /**
     * @return $this
     */
    public function setAsteriskSearchEnabled(bool $asteriskSearchEnabled): self
    {
        $this->asteriskSearchEnabled = $asteriskSearchEnabled;
        return $this;
    }

    protected function queryEscape(string $query): string
    {
        // Replace all non word characters with spaces
        $query = preg_replace('/[^\p{L}\p{N}_*]+/u', ' ', $query);
        // Replace characters-operators with spaces
        $query = preg_replace('/[+\-><\(\)~\"@]+/', ' ', $query);

        if ($this->isAsteriskSearchEnabled()) {
            return preg_replace('/[*]+/', '%', $query);
        }
        return preg_replace('/[*]+/', ' ', $query);
    }
}
