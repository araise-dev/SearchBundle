<?php

declare(strict_types=1);

namespace araise\SearchBundle\Tests;

use araise\SearchBundle\Tests\App\Entity\Company;

class SearchAsteriskTest extends AbstractSearchTest
{
    /**
     * @dataProvider provider
     */
    public function testSearchAsterisk(string $searchQuery, array $expectedNames): void
    {
        $this->createEntities();

        $result = $this->searchManager->searchByEntites($searchQuery, [Company::class]);

        $this->assertCount(count($expectedNames), $result);
        $this->assertCompanyNamesInResult($expectedNames, $result);
    }

    public static function provider(): array
    {
        return [
            'testSearchStartingWith' => [
                'Maur*',
                ['Mauri Company'],
            ],
            'testSearchContaining' => [
                '*ur*',
                ['Mauri Company'],
            ],
            'testSearchEndingWith' => [
                '*BB',
                ['SBB'],
            ],
            'testSearchStartingAndEndingWith' => [
                'Sw*com',
                ['Swisscom'],
            ],

            'testSearchMultipleStartingWith' => [
                'Sun*',
                ['Sunrise', 'Sun Microsystems'],
            ],
            'testSearchMultipleContaining' => [
                '*Comp*',
                ['The Company', 'Mauri Company'],
            ],
            'testSearchMultipleEndingWith' => [
                '*Company',
                ['The Company', 'Mauri Company'],
            ],
            'testSearchMultipleStartingAndEndingWith' => [
                'S*s',
                ['Sun Microsystems', 'Soapstone Networks'],
            ],
        ];
    }
}
