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
                'name' => array('type' => null, 'options' => array('label' => '名称', 'required' => false)),
                'material' => array('type' => null, 'options' => array('label' => '型号', 'required' => false)),
                'color' => array('type' => null, 'options' => array('label' => '颜色', 'required' => false)),
                'specs' => array('type' => null, 'options' => array('label' => '规格', 'required' => false)),
                'price' => array('type' => null, 'options' => array('label' => '价格', 'required' => false)),
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
                'subtype' => array('type' => 'choice', 'options' => array('label' => '显示方式', 'expanded' => true, 'required' => false, 'choices' => array(0 => '嵌入', 1 => '点击全屏浏览'))),
                'imgAssets' => array('type' => 'assets', 'options' => array('label' => '单张图片', 'type' => 'asset', 'nb_max' => 1, 'allow_add' => true, 'prototype' => true, 'allow_delete' => true)),
            ),
            5 => array(
                'imgAssets' => array('type' => 'assets', 'options' => array('label' => '多张图片', 'type' => 'asset', 'allow_add' => true, 'prototype' => true, 'allow_delete' => true)),
            ),
            6 => array(
                'subtype' => array('type' => 'choice', 'options' => array('label' => '链接方式', 'expanded' => true, 'required' => false, 'choices' => array(0 => '内部页面', 1 => '外部链接'))),
                'link' => array('type' => null, 'options' => array('label' => '地址', 'required' => false)),
            ),
            7 => array(
                'subtype' => array('type' => 'choice', 'options' => array('label' => '显示方式', 'expanded' => true, 'required' => false, 'choices' => array(0 => '嵌入', 1 => '点击全屏浏览'))),
                'videoAssets' => array('type' => 'assets', 'options' => array('label' => '视频', 'type' => 'asset', 'nb_max' => 1, 'file_note' => '视频文件', 'file_formats' => 'mp4,avi', 'allow_add' => true, 'prototype' => true, 'allow_delete' => true)),
            ),
            8 => array(
                'subtype' => array('type' => 'choice', 'options' => array('label' => '显示方式', 'expanded' => true, 'required' => false, 'choices' => array(0 => '自动播放', 1 => '手动播放'))),
                'audioAssets' => array('type' => 'assets', 'options' => array('label' => '音频', 'type' => 'asset', 'nb_max' => 1, 'file_note' => '音频文件', 'file_formats' => 'mp3,wav', 'allow_add' => true, 'prototype' => true, 'allow_delete' => true)),
            ),
            9 => array(
                'autoplay' => array('type' => 'checkbox', 'options' => array('label' => '自动播放')),
                'gravity' => array('type' => 'checkbox', 'options' => array('label' => '重力感应')),
                'speed' => array('type' => 'choice', 'options' => array('label' => '滑动速度', 'choices' => array(1, 2, 3), 'attr' => array('class' => 'span1'))),
                'bar' => array('type' => 'checkbox', 'options' => array('label' => '显示控制条')),
                'imgAssets' => array('type' => 'assets', 'options' => array('label' => '序列图片', 'type' => 'asset', 'allow_add' => true, 'prototype' => true, 'allow_delete' => true)),
            ),
            10 => array(
                'address' => array('type' => null, 'options' => array('label' => '地址', 'required' => false)),
                'lat' => array('type' => null, 'options' => array('label' => '经度', 'required' => false)),
                'lng' => array('type' => null, 'options' => array('label' => '纬度', 'required' => false)),
                'subtype' => array('type' => 'choice', 'options' => array('label' => '显示方式', 'expanded' => true, 'required' => false, 'choices' => array(0 => '全屏显示', 1 => '窗口显示'))),
                'btnAssets' => array('type' => 'assets', 'options' => array('label' => '开、关按钮', 'type' => 'asset', 'nb_max' => 2, 'allow_add' => true, 'prototype' => true, 'allow_delete' => true)),
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