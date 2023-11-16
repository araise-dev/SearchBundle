<?php

declare(strict_types=1);

namespace araise\SearchBundle\Tests;

use araise\SearchBundle\DependencyInjection\Configuration;
use araise\SearchBundle\Filter\FilterInterface;
use araise\SearchBundle\Manager\FilterManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

class FilterConfigurationTest extends KernelTestCase
{
    public function testConfig(): void
    {
        $config = Yaml::parse(
            file_get_contents(__DIR__.'/resources/config/basic.yaml')
        );

        $processor = new Processor();
        $databaseConfiguration = new Configuration();
        $processedConfiguration = $processor->processConfiguration(
            $databaseConfiguration,
            $config
        );

        self::assertIsArray($processedConfiguration);
    }

    public function testFilterManagerConfig(): void
    {
        /** @var FilterManager $filterManager */
        $filterManager = self::getContainer()->get(FilterManager::class);

        $config = Yaml::parse(
            file_get_contents(__DIR__.'/resources/config/basic.yaml')
        );

        $processor = new Processor();
        $databaseConfiguration = new Configuration();
        $processedConfiguration = $processor->processConfiguration(
            $databaseConfiguration,
            $config
        );

        foreach ($processedConfiguration['chains'] as $chain => $filters) {
            foreach ($filters['filters'] as $filterConfig) {
                /** @var FilterInterface $filter */
                $filter = new $filterConfig['class']();
                $filter->setOptions($filterConfig['options']);
                $filterManager->addFilter($filter, $chain);
            }
        }

        self::assertTrue(true);
    }
}
