<?php

declare(strict_types=1);

namespace araise\SearchBundle\Tests;

use araise\SearchBundle\Populator\OneFieldPopulator;
use araise\SearchBundle\Populator\PopulatorInterface;
use araise\SearchBundle\Tests\App\Entity\Company;

class SearchTest extends AbstractSearchTest
{
    public function testSearchAll(): void
    {
        $this->createEntities();

        $result = $this->searchManager->searchByEntities('Mauri');

        self::assertCount(6, $result);
    }

    public function testSearchEntity(): void
    {
        $this->createEntities();

        $result = $this->searchManager->searchByEntities('Mauri', [Company::class]);

        self::assertCount(1, $result);
    }

    public function testSearchGroup(): void
    {
        $this->createEntities();

        $result = $this->searchManager->searchByEntities('Mauri', [], ['company']);

        self::assertCount(1, $result);
    }

    protected function setUp($asteriskSearchEnabled = false): void
    {
        parent::setUp($asteriskSearchEnabled);

        /** @var OneFieldPopulator $populator */
        $populator = self::getContainer()->get(OneFieldPopulator::class);
        self::getContainer()->set(PopulatorInterface::class, $populator);
    }
}
