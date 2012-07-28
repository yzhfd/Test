<?php

namespace Magend\HotBundle\Entity;

use Magend\HotBundle\Form\Type\AssetsType;
use Magend\AssetBundle\Entity\Asset;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * HotAttrContainer
 * 
 * 
 * @author Kail
 */
class HotAttrContainer
{
    
    private $hot;
    
    // @todo required => false
    public static $attrsDefs = array(
            1 => array(
                'interaction' => array('type' => null, 'options' => array('label' => '互动效果')),
                'name' => array('type' => null, 'options' => array('label' => '单品名称')),
                'price' => array('type' => null, 'options' => array('label' => '价格')),
                'material' => array('type' => null, 'options' => array('label' => '材质')),
                'specs' => array('type' => null, 'options' => array('label' => '规格')),
            ),
            2 => array(
                'description' => array('type' => null, 'options' => array('label' => '描述图片')),
                'testAssets' => array('type' => 'assets', 'options' => array('label' => '测试图片', 'type' => 'asset', 'file_note' => '图片文件', 'nb_max' => 2, 'allow_add' => true, 'prototype' => true, 'allow_delete' => true)),
                'otherAssets' => array('type' => 'assets', 'options' => array('label' => '更多图片', 'type' => 'asset', 'allow_add' => true, 'prototype' => true, 'allow_delete' => true)),
            ),
            3 => array(
                'description' => array('type' => null, 'options' => array('label' => '描述')),
            ),
    );
    
    private $attrs;
    
    /**
     * If constructed by Hot on load
     * 
     * @param Hot $hot
     */
    public function __construct($hot = null)
    {
        $this->attrs = array();
        $this->hot = $hot;
    }
    
    public function __get($name)
    {
        return isset($this->attrs[$name]) ? $this->attrs[$name] : null;
    }
    
    public function __set($name, $val)
    {
        $this->attrs[$name] = $val;
    }
    
    /**
     * Used in Hot on persist
     * 
     * NOT store assets in attrs
     * 
     * @param integer $hotType
     * @return array
     */
    public function toAttrs($hotType)
    {
        $attrsDefs = self::$attrsDefs;
        $attrs = array();
        foreach ($this->attrs as $name => $attr) {
            if (isset($attrsDefs[$hotType][$name]) && $attrsDefs[$hotType][$name]['type'] != 'assets') {
                $attrs[$name] = $attr;
            }
        }
        return $attrs;
    }
    
    /**
     * Used in Hot on persist
     *
     * @param integer $hotType
     * @return array
     */
    public function getAssets($hotType)
    {
        $attrsDefs = self::$attrsDefs;
        $assets = array();
        foreach ($this->attrs as $name => $attr) {
            if (!isset($attrsDefs[$hotType][$name]) || $attrsDefs[$hotType][$name]['type'] != 'assets' || empty($attr)) {
                continue;
            }
            foreach ($attr as $asset) {
                $asset->setGroupedTo($name);
                $assets[] = $asset;
            }
        }
        
        return $assets;
    }
    
    /**
     * Used in Hot on load
     * 
     * @param Asset $asset
     */
    public function addAsset($asset)
    {
        $groupedTo = $asset->getGroupedTo();
        if (!isset($this->attrs[$groupedTo])) {
            $this->attrs[$groupedTo] = new ArrayCollection();
        }
        $this->attrs[$groupedTo][] = $asset;
    }
}