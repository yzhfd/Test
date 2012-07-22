<?php

namespace Magend\HotBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class HotContainer
{
    public static $hotsDefs = array(
            1 => array('name' => 'furnitureDetailHots',  'label' => '单品家具展示热区'),
            2 => array('name' => 'slideLayerHots', 'label' => '滑动图层热区'),
            3 => array('name' => 'furnitureSkinnableHots', 'label' => '家具换肤热区'),
    );
    
    private $hotCollections;
    
    public function __construct()
    {
        $this->hotCollections = array();
        $hotsDefs = self::$hotsDefs;
        foreach ($hotsDefs as $type => $def) {
            $this->hotCollections[$def['name']] = new ArrayCollection();
        }
    }
    
    public function __get($name)
    {
        return $this->hotCollections[$name];
    }
    
    public function __set($name, $val)
    {
        $this->hotCollections[$name] = $val;
    }
    
    public function toHots()
    {
        $hots = array();
        foreach ($this->hotCollections as $hotCollection) {
            foreach ($hotCollection as $hot) {
                $hots[] = $hot;
            }
        }
        
        return $hots;
    }
    
    public function addHot($hot)
    {
        $type = $hot->getType();
        $hotsDefs = self::$hotsDefs;
        $this->hotCollections[$hotsDefs[$type]['name']][] = $hot;
    }
}