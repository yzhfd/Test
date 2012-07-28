<?php

namespace Magend\PageBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Magend\HotBundle\Entity\HotContainer;
use Magend\PageBundle\Entity\Page;

class PageManager
{
    private $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    private function updateHot($hot)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        
        $hotType = $hot->getType();
        $attrContainer = $hot->getAttrContainer();
        $hot->setAttrs($attrContainer->toAttrs($hotType));
        
        $oldAssets = $hot->getAssets();
        $assets = $attrContainer->getAssets($hotType);
        foreach ($assets as $asset) {
            if (!$oldAssets->contains($asset)) {
                $em->persist($asset);
            } else {
                $oldAssets->removeElement($asset);
            }
        }
        foreach ($oldAssets as $oldAsset) {
            $em->remove($oldAsset);
        }
        $hot->setAssets($assets);
    }
    
    public function updatePage($page)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        
        $hotContainer = $page->getHotContainer();
        $hots = $page->getHots();
        $hotsToRemove = array();
        foreach ($hots as $hot) {
            if (!$hotContainer->containsHot($hot)) {
                $hotsToRemove[] = $hot;
            }
            $this->updateHot($hot);
        }
        $page->setHots($hotContainer->toHots());
        
        if (!empty($hotsToRemove)) {
            foreach ($hotsToRemove as $hot) {
                $assets = $hot->getAssets();
                foreach ($assets as $asset) {
                    $em->remove($asset);
                }
                $em->remove($hot);
            }
            unset($hotsToRemove);
        }
        
        $em->flush();
    }
}