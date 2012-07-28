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
    
    /**
     * UpdateHot
     * 
     * hot will be persisted, and updated with 
     * persisting or removing assets respectively 
     * 
     * @param Hot $hot
     */
    private function updateHot($hot)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->persist($hot);
        
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
    
    /**
     * UpdatePage
     * 
     * @param Page $page
     */
    public function updatePage($page)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        
        $hots = $page->getHots();
        $hotsToRemove = array();
        $hotContainer = $page->getHotContainer();
        foreach ($hots as $hot) {
            if (!$hotContainer->containsHot($hot)) {
                $hotsToRemove[] = $hot;
            }
        }
        
        // persist or update hots
        $newHots = $hotContainer->toHots();
        $page->setHots($newHots);
        foreach ($newHots as $hot) {
            $this->updateHot($hot);
        }
        
        // remove hot and its assets
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