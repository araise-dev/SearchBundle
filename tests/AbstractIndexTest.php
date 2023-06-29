<?php

declare(strict_types=1);

namespace araise\SearchBundle\Tests;

use araise\SearchBundle\Tests\App\Entity\Company;
use araise\SearchBundle\Tests\App\Entity\Contact;
use araise\SearchBundle\Tests\App\Factory\CompanyFactory;
use araise\SearchBundle\Tests\App\Factory\ContactFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class AbstractIndexTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    protected function createEntities()
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        CompanyFactory::createMany(10);

        ContactFactory::createMany(100);

        self::assertSame(10, $em->getRepository(Company::class)->count([]));
        self::assertSame(100, $em->getRepository(Contact::class)->count([]));
    }
}
