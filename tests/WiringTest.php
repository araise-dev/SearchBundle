<?php

declare(strict_types=1);

namespace araise\SearchBundle\Tests;

use araise\SearchBundle\Manager\FilterManager;
use araise\SearchBundle\Manager\IndexManager;
use araise\SearchBundle\Manager\SearchManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WiringTest extends KernelTestCase
{
    public function testServiceWiring(): void
    {
        foreach ([
            IndexManager::class,
            SearchManager::class,
            FilterManager::class,
        ] as $serviceClass) {
            self::assertInstanceOf(
                $serviceClass,
                self::getContainer()->get($serviceClass)
            );
        }
    }
}
