<?php

namespace Magend\OperationBundle\EventListener;

use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Magend\MagazineBundle\Entity\Magazine;
use Magend\IssueBundle\Entity\Issue;
use Magend\ArticleBundle\Entity\Article;

/**
 * OperationEventListener
 * 
 * @author kail
 */
class OperationEventListener
{
    private $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();
        if ($entity instanceof Magazine) {
            echo 'magazine';exit;
        }
    }
    
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();
        if ($entity instanceof Magazine) {
        }
    }
    
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        if ($entity instanceof Magazine) {
            
        }
    }
}