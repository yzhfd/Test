<?php

namespace Magend\HotBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Magend\HotBundle\Entity\HotContainer;

class HotContainerType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $hotsDefs = HotContainer::$hotsDefs;
        foreach ($hotsDefs as $type => $def) {
            $builder->add(
                $def['name'],
                'collection',
                array('label' => $def['label'], 'type' => new HotType($type), 'attr' => array('class' => 'hots_group'), 'prototype' => true, 'allow_add' => true, 'allow_delete' => true));
        }
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Magend\HotBundle\Entity\HotContainer',
        );
    }
    
    public function getName()
    {
        return 'HotContainer';
    }
}
