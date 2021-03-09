<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
//comment
/**
 * @property UserPasswordEncoderInterface encoder
 */
class UserPasswordEncoderSubscriber implements EventSubscriber
{

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->encodeUserPassword($args);
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->encodeUserPassword($args);
    }

    private function encodeUserPassword(LifecycleEventArgs $args):void
    {
        $entity = $args->getObject();

        if(!$entity instanceof User){
            return;
        }

        $entity->setPassword($this->encoder->encodePassword($entity, $entity->getPassword()));

    }
}