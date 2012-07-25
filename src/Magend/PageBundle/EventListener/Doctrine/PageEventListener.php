<?php

namespace Magend\PageBundle\EventListener\Doctrine;

use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Magend\PageBundle\Entity\Page;

/**
 * PageEventListener
 * 
 * @author Kail
 */
class PageEventListener
{
    private $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Page) {
            $page = $entity;
            if (empty($page->hotsToRemove)) {
                return;
            }
            $em = $args->getEntityManager();
            foreach ($page->hotsToRemove as $hot) {
                $em->remove($hot);
            }
        }
    }
}