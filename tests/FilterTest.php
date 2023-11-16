<?php

declare(strict_types=1);

namespace araise\SearchBundle\Tests;

use araise\SearchBundle\Filter\LowerCaseFilter;
use araise\SearchBundle\Filter\RemoveFilter;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function testLowerCaseFilter(): void
    {
        $filter = new LowerCaseFilter();

        self::assertSame([
            'data1',
            'data2',
        ], $filter->process([
            'DATA1',
            'DaTa2',
        ]));
    }

    public function testRemoveFilter(): void
    {
        $filter = new RemoveFilter(['data1']);

        self::assertSame([
            'data2',
        ], $filter->process([
            'data1',
            'data2',
        ]));

        self::assertSame([
            'data3',
            'data2',
        ], $filter->process([
            'data3',
            'data2',
        ]));
    }
}
