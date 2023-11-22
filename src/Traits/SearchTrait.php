<?php

declare(strict_types=1);

namespace araise\SearchBundle\Traits;

use araise\SearchBundle\Manager\SearchManager;
use araise\SearchBundle\Model\ResultItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Stopwatch\Stopwatch;

trait SearchTrait
{
    private static $definitionManagerClass = 'araise\CrudBundle\Manager\DefinitionManager';

    private array $searchOptions = [];

    public static function getSubscribedServices(): array
    {
        if (method_exists(get_parent_class(self::class), 'getSubscribedServices')) {
            $services = parent::getSubscribedServices();
        } else {
            $services = [];
        }
        if (class_exists(self::$definitionManagerClass)) {
            $services = array_merge($services, [self::$definitionManagerClass]);
        }

        return $services;
    }

    /**
     * @return int[]
     */
    protected function getLimitChoices(): array
    {
        return [
            25,
            50,
            100,
            200,
        ];
    }

    protected function getSearchTemplate(): string
    {
        return '@araiseSearch/index.html.twig';
    }

    protected function getGlobalResults(Request $request, SearchManager $searchManager, $options = []): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefault(Search::OPT_ENTITY_ORDER, []);
        $resolver->setDefault(Search::OPT_ENTITIES, []);
        $resolver->setDefault(Search::OPT_GROUPS, []);
        $resolver->setDefault(Search::OPT_STOP_WATCH, false);
        $resolver->setDefault(Search::OPT_LINK_TRANSFORMER, function (ResultItem $item) {
            if (class_exists(self::$definitionManagerClass)) {
                $definitionManager = $this->container->get(self::$definitionManagerClass);
                $router = $this->container->get('router');
                try {
                    $definition = $definitionManager->getDefinitionByEntity($item->getEntity());

                    return $router->generate($definition::getRoutePrefix().'_show', [
                        'id' => $item->getId(),
                    ]);
                } catch (\InvalidArgumentException|RouteNotFoundException $e) {
                    // not found
                }
            }

            return null;
        });
        $resolver->setDefault(Search::OPT_NAME_TRANSFORMER, function (ResultItem $item) {
            return (string) $item->getEntity();
        });
        $resolver->setDefault(Search::OPT_TYPE_TRANSFORMER, function (ResultItem $item) {
            if (class_exists(self::$definitionManagerClass)) {
                $definitionManager = $this->container->get(self::$definitionManagerClass);
                try {
                    $definition = $definitionManager->getDefinitionByEntity($item->getEntity());

                    return $definition->getEntityTitle();
                } catch (\InvalidArgumentException|RouteNotFoundException $e) {
                    // not found
                }
            }

            return basename($item->getClass());
        });

        $this->searchOptions = $resolver->resolve($options);

        if ($this->searchOptions[Search::OPT_STOP_WATCH]) {
            $stopWatch = new Stopwatch();
            $stopWatch->start('araiseSearch');
        }
        $searchTerm = $request->query->get('query', '');

        $results = [];
        if (! empty($searchTerm)) {
            $results = $searchManager->searchByEntities(
                $searchTerm,
                $this->searchOptions[Search::OPT_ENTITIES],
                $this->searchOptions[Search::OPT_GROUPS]
            );
        }

        $results = $this->orderResults($results);

        $limit = $request->query->getInt('limit', 25);

        $total = count($results);
        $pages = (int) ceil($total / $limit);
        $currentPage = $request->query->getInt('page', 1);

        $results = array_slice($results, ($currentPage - 1) * $limit, $limit);

        $pagination = [
            'total' => $total,
            'totalResults' => $total,
            'totalPages' => $pages,
            'pages' => $pages,
            'currentPage' => $currentPage,
            'offsetStart' => ($currentPage - 1) * $limit + 1,
            'offsetEnd' => ($currentPage === $pages) ? $total : $limit,
            'limit' => $limit,
            'limit_choices' => $this->getLimitChoices(),
        ];

        $searchHelper = new class($this->searchOptions[Search::OPT_LINK_TRANSFORMER], $this->searchOptions[Search::OPT_NAME_TRANSFORMER], $this->searchOptions[Search::OPT_TYPE_TRANSFORMER]) {
            public function __construct(
                private $link,
                private $name,
                private $type
            ) {
            }

            public function uri(ResultItem $item)
            {
                return ($this->link)($item);
            }

            public function name(ResultItem $item)
            {
                return ($this->name)($item);
            }

            public function type(ResultItem $item)
            {
                return ($this->type)($item);
            }
        };

        $templateParams = [
            'results' => $results,
            'pagination' => $pagination,
            'searchTerm' => $searchTerm,
            'duration' => $this->searchOptions[Search::OPT_STOP_WATCH] ? $stopWatch->start('araiseSearch')->getDuration() : 0,
            'searchHelper' => $searchHelper,
        ];

        return $templateParams;
    }

    private function orderResults(array $results): array
    {
        if (count($this->searchOptions[Search::OPT_ENTITY_ORDER]) > 0) {
            $reorder = [];

            foreach ($results as $result) {
                if (in_array($result->getClass(), $this->searchOptions[Search::OPT_ENTITY_ORDER], true)) {
                    $reorder[array_search($result->getClass(), $this->searchOptions[Search::OPT_ENTITY_ORDER], true)][] = $result;
                } else {
                    $reorder[999][] = $result;
                }
            }

            ksort($reorder);

            // flatten
            $results = [];
            foreach ($reorder as $reorderEntity) {
                foreach ($reorderEntity as $item) {
                    $results[] = $item;
                }
            }
        } else {
            usort($results, fn (ResultItem $a, ResultItem $b) => $a->getScore() - $b->getScore());
        }

        return $results;
    }
}
