<?php

declare(strict_types=1);

namespace araise\SearchBundle\Tests;

use araise\SearchBundle\Filter\LowerCaseFilter;
use araise\SearchBundle\Manager\FilterManager;
use araise\SearchBundle\Tokenizer\StandardTokenizer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class FilterManagerTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    public function testFilter(): void
    {
        /** @var FilterManager $filterManager */
        $filterManager = self::getContainer()->get(FilterManager::class);

        $filterManager->addTokenizer(new StandardTokenizer(), 'defaultChain');
        $filter = new LowerCaseFilter();
        $filter->setOptions([]);
        $filterManager->addFilter($filter, 'defaultChain');

        self::assertSame(
            'data1 data2',
            $filterManager->process('DATA1 DaTa2', 'defaultChain')
        );
    }

    public function testStandardTokenizer(): void
    {
        /** @var FilterManager $filterManager */
        $filterManager = self::getContainer()->get(FilterManager::class);

        $filter = new LowerCaseFilter();
        $filter->setOptions([]);
        $filterManager->addFilter($filter, 'defaultChain');

        self::assertSame(
            'data1 data2',
            $filterManager->process('DATA1 DaTa2', 'defaultChain')
        );
    }

    public function testNoChainDefined(): void
    {
        /** @var FilterManager $filterManager */
        $filterManager = self::getContainer()->get(FilterManager::class);

        $this->expectExceptionMessage('FilterChain "testChain" not configured');

        self::assertSame(
            'data1 data2',
            $filterManager->process('DATA1 DaTa2', 'testChain')
        );
    }
}
