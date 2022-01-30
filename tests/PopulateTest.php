<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use whatwedo\SearchBundle\Entity\Index;
use whatwedo\SearchBundle\Exception\ClassNotDoctrineMappedException;
use whatwedo\SearchBundle\Exception\ClassNotIndexedEntityException;
use whatwedo\SearchBundle\Populator\PopulatorInterface;
use whatwedo\SearchBundle\Populator\StandardPopulator;
use whatwedo\SearchBundle\Tests\Fixtures\Entity\Company;
use whatwedo\SearchBundle\Tests\Fixtures\Entity\Person;
use whatwedo\SearchBundle\Tests\Fixtures\Model\NotADoctrinieModel;

class PopulateTest extends AbstractSearchTest
{
    public function testPopulate()
    {
        $this->_resetSchema();
        $this->_resetDatabase();

        $this->createEntities();

        /** @var PopulatorInterface $populator */
        $populator = self::getContainer()->get(StandardPopulator::class);

        $populator->populate();

        $this->assertSame(1100, self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Index::class)->count([]));
    }

    public function testPopulateCompanies()
    {
        $this->_resetSchema();
        $this->_resetDatabase();

        $this->createEntities();

        /** @var PopulatorInterface $populator */
        $populator = self::getContainer()->get(StandardPopulator::class);

        $populator->populate(null, Company::class);

        $this->assertSame(100, self::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Index::class)->count([]));
    }


    public function testPopulateNotEntity()
    {
        /** @var PopulatorInterface $populator */
        $populator = self::getContainer()->get(StandardPopulator::class);

        $this->expectException(ClassNotDoctrineMappedException::class);

        $populator->populate(null, NotADoctrinieModel::class);

    }

    public function testPopulateNotIndexEntity()
    {
        /** @var PopulatorInterface $populator */
        $populator = self::getContainer()->get(StandardPopulator::class);

        $this->expectException(ClassNotIndexedEntityException::class);

        $populator->populate(null, Person::class);

    }

}
