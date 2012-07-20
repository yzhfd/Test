<?php

namespace Magend\HotBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Magend\KeywordBundle\Form\KeywordType;

// prototype, container, vessel
class DynamicType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
             ->add('name', null, array('label' => 'name'))
             ->add('val', 'choice', array('label' => 'val', 'choices' => array('aaa', 'bbb', 'ccc')))
        ;
    }

    public function getDefaultOptions(array $options)
    {
        return array(
                'choices' => array(
                        'm' => 'Male',
                        'f' => 'Female',
                )
        );
    }
    
    public function getName()
    {
        return 'dynamic';
    }
    
    public function getParent(array $options)
    {
        return 'form';
    }
}
