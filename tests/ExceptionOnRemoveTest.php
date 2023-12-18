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

namespace araise\SearchBundle\Tests;

use araise\SearchBundle\Manager\SearchManager;
use araise\SearchBundle\Tests\App\Entity\Computer;
use araise\SearchBundle\Tests\App\Entity\Disk;
use araise\SearchBundle\Tests\App\Factory\DiskFactory;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class ExceptionOnRemoveTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private EntityManagerInterface $entityManager;

    private SearchManager $searchManager;

    private Disk $disk;

    private string $computerName;

    public function testDoesRemoveFromIndexWhenNoException(): void
    {
        $this->entityManager->remove($this->disk);
        $this->entityManager->flush();
        $result = $this->searchManager->searchByEntities($this->computerName, [Computer::class]);
        self::assertCount(0, $result);
    }

    public function testDoesNotRemoveFromIndexWhenExceptionWhileDeleting(): void
    {
        $failed = false;
        try {
            $this->entityManager->flush();
        } catch (ForeignKeyConstraintViolationException $e) {
            $failed = true;
        }
        self::assertTrue($failed);
        $result = $this->searchManager->searchByEntities($this->computerName, [Computer::class]);
        self::assertCount(1, $result);
    }

    protected function setUp(): void
    {
        $this->disk = DiskFactory::createOne()->object();
        self::assertNotNull($this->disk->getId());
        self::assertNotNull($this->disk->getComputer()->getId());
        $this->computerName = $this->disk->getComputer()->getName();
        $searchManager = self::getContainer()->get(SearchManager::class);
        self::assertInstanceOf(SearchManager::class, $searchManager);
        $this->searchManager = $searchManager;
        $result = $this->searchManager->searchByEntities($this->computerName, [Computer::class]);
        self::assertCount(1, $result);
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $this->entityManager = $entityManager;
        $this->entityManager->remove($this->disk->getComputer());
    }
}
