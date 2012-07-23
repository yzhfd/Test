<?php

namespace Magend\PageBundle\EventListener\Doctrine;

use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Magend\PageBundle\Entity\Page;

/**
 * PageEventListener
 * 
 * @author kail
 */
class PageEventListener
{
    private $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        
        foreach ($uow->getScheduledCollectionUpdates() AS $col) {
        }
        
        foreach ($uow->getScheduledEntityInsertions() AS $entity) {
        }
        
        foreach ($uow->getScheduledEntityUpdates() AS $entity) {
            die('x');
            if ($entity instanceof Page) {
                $hots = $entity->getHots();
                foreach ($hots as $hot) {
                    $em->persist($hot);
                }
                exit;
            }
        }

        foreach ($uow->getScheduledEntityDeletions() AS $entity) {

        }

        foreach ($uow->getScheduledCollectionDeletions() AS $col) {

        }
    }
}