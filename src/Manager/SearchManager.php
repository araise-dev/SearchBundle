<?php

declare(strict_types=1);

namespace araise\SearchBundle\Manager;

use araise\SearchBundle\Model\ResultItem;
use araise\SearchBundle\Repository\IndexRepository;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\ORM\EntityManagerInterface;

class SearchManager
{
    public function __construct(
        private readonly IndexRepository $indexRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return ResultItem[]
     * @throws AnnotationException
     * @throws \ReflectionException
     */
    public function searchByEntities(string $searchTerm, array $entityFqcns = [], array $groups = []): array
    {
        $indexResults = $this->indexRepository->searchEntities($searchTerm, $entityFqcns, $groups);
        $loadedEntities = $this->loadEntities($indexResults);
        $entities = [];

        foreach ($indexResults as $searchResult) {
            $entities[] =
                new ResultItem(
                    $searchResult['id'],
                    $searchResult['model'],
                    (float) $searchResult['_matchQuote'],
                    $loadedEntities[$searchResult['model']][$searchResult['id']]
                );
        }

        return $entities;
    }

    protected function groupByClass(array $indexResults): array
    {
        $groupByClass = [];
        foreach ($indexResults as $searchResult) {
            if (! isset($groupByClass[$searchResult['model']])) {
                $groupByClass[$searchResult['model']] = [];
            }

            $groupByClass[$searchResult['model']][] = $searchResult['id'];
        }

        return $groupByClass;
    }

    private function loadEntities(array $indexResults): array
    {
        $groupedEntities = [];

        foreach ($this->groupByClass($indexResults) as $class => $ids) {
            $result = $this->entityManager->getRepository($class)->findBy([
                'id' => $ids,
            ]);

            $data = [];
            foreach ($result as $item) {
                $data[$item->getId()] = $item;
            }

            $groupedEntities[$class] = $data;
        }

        return $groupedEntities;
    }
}
