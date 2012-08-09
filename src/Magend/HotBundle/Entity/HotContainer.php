<?php

namespace Magend\HotBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class HotContainer
{
    public static $hotsDefs = array(
            1 => array('name' => 'furnitureDetailHots',  'label' => '单品家具展示热区', 'span' => '单品家具'),
            2 => array('name' => 'slideLayerHots', 'label' => '滑动图层热区', 'span' => '滑动图层'),
            3 => array('name' => 'furnitureSkinnableHots', 'label' => '家具换肤热区', 'span' => '家具换肤'),
            4 => array('name' => 'singleImageHots', 'label' => '单图热区', 'span' => '单图', 'class' => 'hotsimg'),
            5 => array('name' => 'multiImagesHots', 'label' => '多图热区', 'span' => '多图', 'class' => 'hotimgs'),
            6 => array('name' => 'linkHots', 'label' => '链接热区', 'span' => '链接', 'class' => 'hotlink'),
            7 => array('name' => 'videoHots', 'label' => '视频热区', 'span' => '视频', 'class' => 'hotvideo'),
            8 => array('name' => 'audioHots', 'label' => '音频热区', 'span' => '音频', 'class' => 'hotaudio'),
            9 => array('name' => 'seqImagesHots', 'label' => '序列图热区', 'span' => '序列图', 'class' => 'hotseq'),
            10 => array('name' => 'mapHots', 'label' => '地图热区', 'span' => '地图', 'class' => 'hotmap'),
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
    
    public function containsHot($hot)
    {
        foreach ($this->hotCollections as $hotCollection) {
            if ($hotCollection->contains($hot)) {
                return true;
            }
        }
        
        return false;
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