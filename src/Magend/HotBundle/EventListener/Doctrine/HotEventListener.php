<?php

namespace Magend\HotBundle\EventListener\Doctrine;

use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Magend\HotBundle\Entity\Hot;

/**
 * HotEventListener
 * 
 * @author Kail
 */
class HotEventListener
{
    private $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Hot) {
            $em = $args->getEntityManager();
            $hot = $entity;
            
            $hotType = $hot->getType();
            $hot->setAttrs($hot->attrContainer->toAttrs($hotType));
            $assets = $hot->attrContainer->getAssets($hotType);
            foreach ($assets as $asset) {
                $em->persist($asset);
            }
            
            $hot->setAssets($assets);
            
            /*if (empty($page->hotsToRemove)) {
                return;
            }
            
            foreach ($page->hotsToRemove as $hot) {
                $em->remove($hot);
            }*/
        }
    }
}