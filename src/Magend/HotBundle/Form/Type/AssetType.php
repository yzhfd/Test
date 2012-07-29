<?php

namespace Magend\HotBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

/**
 * 
 * 
 * @author Kail
 */
class AssetType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
             ->add('seq', 'hidden', array('label' => '顺序', 'attr' => array('class' => 'asset_order')))
             ->add('tag', 'hidden', array('label' => '标签', 'attr' => array('class' => 'asset_tag')))
             ->add('resource', 'hidden', array('label' => '文件名', 'attr' => array('class' => 'asset_resource')))
        ;
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Magend\AssetBundle\Entity\Asset',
        );
    }
    
    public function getName()
    {
        return 'asset';
    }
}
