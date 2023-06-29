<?php

declare(strict_types=1);

namespace araise\SearchBundle\Tests;

use PHPUnit\Framework\TestCase;
use araise\SearchBundle\Tokenizer\StandardTokenizer;

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
