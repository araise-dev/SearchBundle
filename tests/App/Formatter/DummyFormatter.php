<?php

declare(strict_types=1);

namespace araise\SearchBundle\Tests\App\Formatter;

use araise\CoreBundle\Formatter\AbstractFormatter;

class DummyFormatter extends AbstractFormatter
{
    public function getString($value): string
    {
        return 'dummy';
    }
}
