<?php

declare(strict_types=1);

namespace araise\SearchBundle\Tests;

use araise\SearchBundle\Tokenizer\StandardTokenizer;
use PHPUnit\Framework\TestCase;

class TokenizerTest extends TestCase
{
    public function testLowerCaseFilter()
    {
        $tokeizer = new StandardTokenizer();

        self::assertSame([
            'DATA1',
            'DaTa2',
        ], $tokeizer->tokenize('DATA1 DaTa2'));
    }
}
