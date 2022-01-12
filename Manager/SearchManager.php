<?php

namespace whatwedo\SearchBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use whatwedo\SearchBundle\Model\ResultItem;
use whatwedo\SearchBundle\Repository\IndexRepository;

class SearchManager
{
    public function __construct(
        private IndexRepository $indexRepository,
        private EntityManagerInterface $entityManager
    )
    {
    }

    /**
     * @return array|ResultItem[]
     */
    public function search(string $searchTerm) {

        $indexResults = $this->indexRepository->searchEntities($searchTerm);
        $groupedEntities = $this->loadEntities($indexResults);
        $entities = [];

        foreach ($indexResults as $searchResult) {
            $entities[] =
                new ResultItem(
                    $searchResult['id'],
                    $searchResult['model'],
                    $searchResult['_matchQuote'],
                    $groupedEntities[$searchResult['model']][$searchResult['id']]
                );
        }

        return $entities;
    }

    /**
     * @param array $indexResults
     * @return array
     */
    protected function groupByClass(array $indexResults): array
    {
        $groupByClass = [];
        foreach ($indexResults as $searchResult) {
            if (!isset($groupByClass[$searchResult['model']])) {
                $groupByClass[$searchResult['model']] = [];
            }

            $groupByClass[$searchResult['model']][] = $searchResult['id'];
        }
        return $groupByClass;
    }

    private function loadEntities(array $indexResults) {
        $groupedEntities = [];

        foreach ($this->groupByClass($indexResults) as $class => $ids) {
            $result = $this->entityManager->getRepository($class)->findBy(['id' => $ids]);

            $data = [];
            foreach ($result as $item) {
                $data[$item->getId()] = $item;
            }

            $groupedEntities[$class] = $data;
        }

        return $groupedEntities;
    }
}
