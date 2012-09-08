<?php

namespace Magend\HotBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Magend\HotBundle\Entity\HotAttrContainer;

/**
 * 
 * @author Kail
 */
class HotAttrContainerType extends AbstractType
{
    private $hotType;
    
    public function __construct($hotType)
    {
        $this->hotType = $hotType;
    }
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        $attrsDefs = HotAttrContainer::$attrsDefs;
        $def = $attrsDefs[$this->hotType];
        foreach ($def as $name => $arr) {
            $builder->add($name, $arr['type'], $arr['options']);
        }
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Magend\HotBundle\Entity\HotAttrContainer',
        );
    }
    
    public function getName()
    {
        return 'HotAttrContainer';
    }
}
