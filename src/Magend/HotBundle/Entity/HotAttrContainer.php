<?php

namespace Magend\HotBundle\Entity;

use Magend\HotBundle\Form\Type\AssetsType;

class HotAttrContainer
{
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
                'testAssets' => array('type' => 'assets', 'options' => array('label' => '测试资源', 'type' => 'text')),
            ),
            3 => array(
                'description' => array('type' => null, 'options' => array('label' => '描述')),
            ),
    );
    
    private $attrs;
    
    public function __construct()
    {
        $this->attrs = array();
    }
    
    public function __get($name)
    {
        return isset($this->attrs[$name]) ? $this->attrs[$name] : null;
    }
    
    public function __set($name, $val)
    {
        $this->attrs[$name] = $val;
    }
    
    public function toAttrs()
    {
        return $this->attrs;
    }
}