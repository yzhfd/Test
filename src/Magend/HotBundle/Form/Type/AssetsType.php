<?php

namespace Magend\HotBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

/**
 * 
 * 
 * @author Kail
 */
class AssetsType extends AbstractType
{
    public function getDefaultOptions(array $options)
    {
        return array(
            'nb_max' => null,
            'file_note' => '图片文件',
            'file_formats' => 'jpg,jpeg,png'
        );
    }
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        // override to use $$asset_name$$ as prototype's placeholder instead of $$name$$
        if ($options['allow_add'] && $options['prototype']) {
            $prototype = $builder->create('$$asset_name$$', $options['type'], $options['options']);
            $builder->setAttribute('prototype', $prototype->getForm());
        }
        
        $builder
            ->setAttribute('nb_max', $options['nb_max'])
            ->setAttribute('file_note', $options['file_note'])
            ->setAttribute('file_formats', $options['file_formats'])
        ;
    }
    
    public function buildView(FormView $view, FormInterface $form)
    {
        $view
            ->set('nb_max', $form->getAttribute('nb_max'))
            ->set('file_note', $form->getAttribute('file_note'))
            ->set('file_formats', $form->getAttribute('file_formats'))
        ;
    }
    
    public function getName()
    {
        return 'assets';
    }
    
    public function getParent(array $options)
    {
        return 'collection';
    }
}
