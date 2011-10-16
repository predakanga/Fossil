<?php

/*
 * Copyright (c) 2011, predakanga
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the <organization> nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace Fossil\Plugins\Search\Subscribers;

use Fossil\OM,
    Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Events,
    Doctrine\ORM\Event\OnFlushEventArgs,
    Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Description of UpdateListener
 *
 * @author predakanga
 */
class SearchSubscriber implements EventSubscriber {
    public function getSubscribedEvents()
    {
        return array(Events::onFlush,
                     Events::postPersist);
    }
    
    public function onFlush(OnFlushEventArgs $eventArgs) {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();
        $needsFlush = false;

        foreach ($uow->getScheduledEntityUpdates() AS $entity) {
            if($entity instanceof ISearchable) {
                OM::Search()->updateEntity($entity);
                $needsFlush = true;
            }
        }

        foreach ($uow->getScheduledEntityDeletions() AS $entity) {
            if($entity instanceof ISearchable) {
                OM::Search()->removeEntity($entity);
                $needsFlush = true;
            }
        }
        if($needsFlush)
            OM::Search()->commit();
    }
    
    public function postPersist(LifecycleEventArgs $eventArgs) {
        $entity = $eventArgs->getEntity();
        if($entity instanceof \Fossil\Plugins\Search\ISearchable) {
            OM::Search()->indexEntity($entity);
        }
    }
}

?>
