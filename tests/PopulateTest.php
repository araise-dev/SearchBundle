<?php

declare(strict_types=1);

namespace araise\SearchBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use araise\SearchBundle\Entity\Index;
use araise\SearchBundle\Exception\ClassNotDoctrineMappedException;
use araise\SearchBundle\Exception\ClassNotIndexedEntityException;
use araise\SearchBundle\Populator\OneFieldPopulator;
use araise\SearchBundle\Populator\PopulatorInterface;
use araise\SearchBundle\Populator\StandardPopulator;
use araise\SearchBundle\Tests\App\Entity\Company;
use araise\SearchBundle\Tests\App\Entity\Person;
use araise\SearchBundle\Tests\App\Model\NotADoctrinieModel;

class PopulateTest extends AbstractIndexTest
{
    public function testPopulate()
    {
        $populator = self::getContainer()->get(PopulatorInterface::class);
        $populator->resetVisited();

        $this->createEntities();

        self::assertSame(140, self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Index::class)->count([]));
    }

    public function testPopulateCompanies()
    {
        $this->createEntities();

        /** @var PopulatorInterface $populator */
        $populator = self::getContainer()->get(PopulatorInterface::class);

        $populator->populate(null, Company::class);

        self::assertSame(40, self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Index::class)->count([]));
    }

    public function testPopulateNotEntity()
    {
        /** @var PopulatorInterface $populator */
        $populator = self::getContainer()->get(PopulatorInterface::class);

        $this->expectException(ClassNotDoctrineMappedException::class);

        $populator->populate(null, NotADoctrinieModel::class);
    }

    public function testPopulateNotIndexEntity()
    {
        /** @var PopulatorInterface $populator */
        $populator = self::getContainer()->get(PopulatorInterface::class);

        $this->expectException(ClassNotIndexedEntityException::class);

        $populator->populate(null, Person::class);
    }

    public function testDisablePopulate()
    {
        /** @var PopulatorInterface $populator */
        $populator = self::getContainer()->get(PopulatorInterface::class);
        $populator->disableEntityListener(true);

        $this->createEntities();

        self::assertSame(0, self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Index::class)->count([]));
    }

    protected function setUp(): void
    {
        /** @var OneFieldPopulator $populator */
        $populator = self::getContainer()->get(StandardPopulator::class);
        self::getContainer()->set(PopulatorInterface::class, $populator);
    }
}
