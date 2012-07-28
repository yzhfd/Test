<?php

namespace Magend\HotBundle\EventListener\Doctrine;

use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Magend\HotBundle\Entity\Hot;
use Magend\AssetBundle\Entity\Asset;

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
    
    /*public function preUpdate(LifecycleEventArgs $args)
    {
        $this->preSave($args);
    }*/
    
    /*public function prePersist(LifecycleEventArgs $args)
    {
        $this->preSave($args);
    }*/
    
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Hot) {
            $em = $args->getEntityManager();
            $hot = $entity;
            /*
            $hotType = $hot->getType();
            $hot->setAttrs($hot->attrContainer->toAttrs($hotType));
            $assets = $hot->attrContainer->getAssets($hotType);
            $hot->setAssets($assets);
            foreach ($assets as $asset) {
                $em->persist($asset);
            }*/
            
            /*if (empty($page->hotsToRemove)) {
                return;
            }
            
            foreach ($page->hotsToRemove as $hot) {
                $em->remove($hot);
            }*/
        }
    }
}