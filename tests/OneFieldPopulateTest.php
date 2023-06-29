<?php

declare(strict_types=1);

namespace araise\SearchBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use araise\SearchBundle\Entity\Index;
use araise\SearchBundle\Populator\OneFieldPopulator;
use araise\SearchBundle\Populator\PopulatorInterface;

class OneFieldPopulateTest extends AbstractIndexTest
{
    public function testPopulate()
    {
        /** @var OneFieldPopulator $populator */
        $populator = self::getContainer()->get(PopulatorInterface::class);

        $this->createEntities();

        $populator->populate();

        self::assertSame(330, self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Index::class)->count([]));
    }

    public function testListnerPopulate()
    {
        $this->createEntities();

        self::assertSame(330, self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Index::class)->count([]));
    }

    public function testDisableListnerPopulate()
    {
        /** @var OneFieldPopulator $populator */
        $populator = self::getContainer()->get(PopulatorInterface::class);

        $populator->disableEntityListener(true);

        $this->createEntities();

        self::assertSame(0, self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Index::class)->count([]));
    }

    protected function setUp(): void
    {
        /** @var OneFieldPopulator $populator */
        $populator = self::getContainer()->get(OneFieldPopulator::class);
        self::getContainer()->set(PopulatorInterface::class, $populator);
    }
}
