<?php

declare(strict_types=1);

namespace araise\SearchBundle\Tests;

use araise\SearchBundle\Manager\SearchManager;
use araise\SearchBundle\Model\ResultItem;
use araise\SearchBundle\Tests\App\Entity\Company;
use araise\SearchBundle\Tests\App\Factory\CompanyFactory;
use araise\SearchBundle\Tests\App\Factory\ContactFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class AbstractSearchTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    protected SearchManager $searchManager;

    protected function createEntities()
    {
        CompanyFactory::createOne([
            'name' => 'whatwedo GmbH',
            'city' => 'Bern',
            'country' => 'Switzerland',
            'taxIdentificationNumber' => '001.002.003',
        ]);
        CompanyFactory::createOne([
            'name' => 'Swisscom',
            'city' => 'Bern',
            'country' => 'Switzerland',
            'taxIdentificationNumber' => '001.002.004',
        ]);
        CompanyFactory::createOne([
            'name' => 'SBB',
            'city' => 'Bern',
            'country' => 'Switzerland',
            'taxIdentificationNumber' => '001.002.005',
        ]);
        CompanyFactory::createOne([
            'name' => 'Sunrise',
            'city' => 'Zürich',
            'country' => 'Switzerland',
            'taxIdentificationNumber' => '001.002.008',
        ]);
        CompanyFactory::createOne([
            'name' => 'Sun Microsystems',
            'city' => 'California',
            'country' => 'USA',
            'taxIdentificationNumber' => '001.002.003',
        ]);
        CompanyFactory::createOne([
            'name' => 'Soapstone Networks',
            'city' => 'Massachusetts',
            'country' => 'USA',
            'taxIdentificationNumber' => '001.002.003',
        ]);
        CompanyFactory::createOne([
            'name' => 'The Company',
            'city' => 'Los Angeles',
            'country' => 'USA',
            'taxIdentificationNumber' => '001.001.003',
        ]);
        CompanyFactory::createOne([
            'name' => 'Mauri Company',
            'city' => 'Bümpliz',
            'country' => 'Switzerland',
            'taxIdentificationNumber' => '001.001.003',
        ]);

        ContactFactory::createOne([
            'name' => 'Maurizio Monticelli',
            'company' => CompanyFactory::findOrCreate([
                'name' => 'whatwedo GmbH',
            ]),
        ]);
        ContactFactory::createOne([
            'name' => 'Maurizio Monticelli',
            'company' => CompanyFactory::findOrCreate([
                'name' => 'Swisscom',
            ]),
        ]);
        ContactFactory::createOne([
            'name' => 'Maurizio Monticelli',
            'company' => CompanyFactory::findOrCreate([
                'name' => 'SBB',
            ]),
        ]);
        ContactFactory::createOne([
            'name' => 'Maurizio Monticelli',
            'company' => CompanyFactory::findOrCreate([
                'name' => 'Sunrise',
            ]),
        ]);
        ContactFactory::createOne([
            'name' => 'Maurizio Monticelli',
            'company' => CompanyFactory::findOrCreate([
                'name' => 'The Company',
            ]),
        ]);
    }

    /**
     * @param ResultItem[] $actualResultItems
     */
    protected function assertCompanyNamesInResult($expectedNames, array $actualResultItems): void
    {
        foreach ($actualResultItems as $item) {
            $entity = $item->getEntity();

            $this->assertInstanceOf(Company::class, $entity);
            $this->assertContains($entity->getName(), $expectedNames);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        /** @var SearchManager $searchManager */
        $searchManager = self::getContainer()->get(SearchManager::class);
        $this->searchManager = $searchManager;
    }
}
