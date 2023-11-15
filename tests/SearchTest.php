<?php

declare(strict_types=1);

namespace araise\SearchBundle\Tests;

use araise\SearchBundle\Populator\OneFieldPopulator;
use araise\SearchBundle\Populator\PopulatorInterface;
use araise\SearchBundle\Tests\App\Entity\Company;

class SearchTest extends AbstractSearchTest
{
    public function testSearchAll()
    {
        $this->createEntities();

        $result = $this->searchManager->searchByEntites('Mauri');

        self::assertCount(6, $result);
    }

    public function testSearchEntity()
    {
        $this->createEntities();

        $result = $this->searchManager->searchByEntites('Mauri', [Company::class]);

        self::assertCount(1, $result);
    }

    public function testSearchGroup()
    {
        $this->createEntities();

        $result = $this->searchManager->searchByEntites('Mauri', [], ['company']);

        self::assertCount(1, $result);
    }

    protected function setUp(): void
    {
        parent::setUp();

        /** @var OneFieldPopulator $populator */
        $populator = self::getContainer()->get(OneFieldPopulator::class);
        self::getContainer()->set(PopulatorInterface::class, $populator);
    }
}
