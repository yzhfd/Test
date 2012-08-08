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
    
    // @todo 'required' => false
    public static $attrsDefs = array(
            1 => array(
                'interaction' => array('type' => null, 'options' => array('label' => '互动效果', 'required' => false)),
                'name' => array('type' => null, 'options' => array('label' => '单品名称', 'required' => false)),
                'price' => array('type' => null, 'options' => array('label' => '价格', 'required' => false)),
                'material' => array('type' => null, 'options' => array('label' => '材质', 'required' => false)),
                'specs' => array('type' => null, 'options' => array('label' => '规格', 'required' => false)),
                'furnitureAssets' => array('type' => 'assets', 'options' => array('label' => '家具图片', 'type' => 'asset', 'allow_add' => true, 'prototype' => true, 'allow_delete' => true)),
            ),
            2 => array(
                'direction' => array('type' => 'choice', 'options' => array('label' => '导引图位置', 'attr' => array('class' => 'span1'), 'required' => false, 'choices' => array('0' => '上', '1' => '下', '2' => '左', '3' => '右'))),
                'guideAssets' => array('type' => 'assets', 'options' => array('label' => '导引图片', 'type' => 'asset', 'file_note' => '图片文件', 'nb_max' => 2, 'allow_add' => true, 'prototype' => true, 'allow_delete' => true)),
                'descriptionAssets' => array('type' => 'assets', 'options' => array('label' => '描述图片', 'type' => 'asset', 'nb_max' => 1, 'allow_add' => true, 'prototype' => true, 'allow_delete' => true)),
            ),
            3 => array(
                 'colorx' => array('type' => null, 'options' => array('label' => '替换横坐标', 'data' => 0, 'attr' => array('class' => 'span2'))),
                 'colory' => array('type' => null, 'options' => array('label' => '替换纵坐标', 'data' => 0, 'attr' => array('class' => 'span2'))),
                'imgAssets' => array('type' => 'assets', 'options' => array('label' => '图片', 'type' => 'asset', 'allow_add' => true, 'prototype' => true, 'allow_delete' => true)),
            ),
            4 => array(
            ),
            5 => array(
            ),
            6 => array(
            ),
            7 => array(
            ),
            8 => array(
            ),
            9 => array(
            ),
            10 => array(
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