<?php

declare(strict_types=1);
/**
 * Copyright (c) 2016, whatwedo GmbH
 * All rights reserved.
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

namespace araise\SearchBundle\EventListener;

use araise\CoreBundle\Manager\FormatterManager;
use araise\SearchBundle\Manager\IndexManager;
use araise\SearchBundle\Populator\PopulatorInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::preRemove)]
class IndexListener implements EventSubscriber
{
    public function __construct(
        protected IndexManager $indexManager,
        protected FormatterManager $formatterManager,
        private PopulatorInterface $populator
    ) {
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     */
    public function getSubscribedEvents(): array
    {
        return [
            'postPersist',
            'postUpdate',
            'preRemove',
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->populator->index($args->getObject());
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->populator->index($args->getObject());
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        $this->populator->remove($args->getObject());
    }
}
