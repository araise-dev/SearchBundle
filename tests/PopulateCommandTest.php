<?php

declare(strict_types=1);

namespace araise\SearchBundle\Tests;

use araise\SearchBundle\Entity\Index;
use Doctrine\ORM\EntityManagerInterface;
use Zenstruck\Console\Test\InteractsWithConsole;

class PopulateCommandTest extends AbstractIndexTest
{
    use InteractsWithConsole;

    public function testPopulateCommand(): void
    {
        $this->createEntities();

        $this->executeConsoleCommand('araise:search:populate')
            ->assertSuccessful()
            ->assertOutputContains('Flushing index table')
            ->assertOutputContains('Entity\Company')
            ->assertOutputContains('Entity\Contact')
        ;

        self::assertSame(330, self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Index::class)->count([]));
    }
}
