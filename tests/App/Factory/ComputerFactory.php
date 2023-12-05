<?php

declare(strict_types=1);
/*
 * Copyright (c) 2023, whatwedo GmbH
 * All rights reserved
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace araise\SearchBundle\Tests\App\Factory;

use araise\SearchBundle\Tests\App\Entity\Computer;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @method static        Computer|Proxy createOne(array $attributes = [])
 * @method static        Computer[]|Proxy[] createMany(int $number, $attributes = [])
 * @method static        Computer|Proxy find($criteria)
 * @method static        Computer|Proxy findOrCreate(array $attributes)
 * @method static        Computer|Proxy first(string $sortedField = 'id')
 * @method static        Computer|Proxy last(string $sortedField = 'id')
 * @method static        Computer|Proxy random(array $attributes = [])
 * @method static        Computer|Proxy randomOrCreate(array $attributes = [])
 * @method static        Computer[]|Proxy[] all()
 * @method static        Computer[]|Proxy[] findBy(array $attributes)
 * @method static        Computer[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static        Computer[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static        RepositoryProxy repository()
 * @method Computer|Proxy create($attributes = [])
 */
class ComputerFactory extends ModelFactory
{
    protected static function getClass(): string
    {
        return Computer::class;
    }

    protected function getDefaults(): array
    {
        return [
            'name' => self::faker()->colorName().'-'.self::faker()->randomNumber(3),
        ];
    }
}
