<?php

declare(strict_types=1);

namespace araise\SearchBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use araise\SearchBundle\Manager\FilterManager;
use araise\SearchBundle\Manager\IndexManager;
use araise\SearchBundle\Manager\SearchManager;

class WiringTest extends KernelTestCase
{
    public function testServiceWiring()
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
