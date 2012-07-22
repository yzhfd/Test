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
                array('label' => $def['label'], 'type' => new HotType($type), 'prototype' => true, 'allow_add' => true));
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
