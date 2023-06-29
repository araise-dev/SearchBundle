<?php

declare(strict_types=1);

use araise\CoreBundle\araiseCoreBundle;
use araise\SearchBundle\araiseSearchBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Zenstruck\Foundry\ZenstruckFoundryBundle;

return [
    FrameworkBundle::class => [
        'all' => true,
    ],
    DoctrineBundle::class => [
        'all' => true,
    ],
    TwigBundle::class => [
        'all' => true,
    ],
    ZenstruckFoundryBundle::class => [
        'all' => true,
    ],
    araiseCoreBundle::class => [
        'all' => true,
    ],
    araiseSearchBundle::class => [
        'all' => true,
    ],
];
