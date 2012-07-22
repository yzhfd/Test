<?php

namespace Magend\HotBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

/**
 * Base type for Hot
 * 
 * @author Kail
 */
class HotType extends AbstractType
{
    private $type;
    
    public function __construct($type)
    {
        $this->type = $type;
    }
    
    //@todo __construct
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
             ->add('type', 'hidden', array('label' => '类型'))
             ->add('x', null, array('label' => '横坐标'))
             ->add('y', null, array('label' => '纵坐标'))
             ->add('w', null, array('label' => '宽'))
             ->add('h', null, array('label' => '高'))
             ->add('attrContainer', new HotAttrContainerType($this->type), array('label' => '属性'))
        ;
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Magend\HotBundle\Entity\Hot',
        );
    }
    
    public function getName()
    {
        return 'hot';
    }
}
