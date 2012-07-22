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
            'multiple' => true,
            'file_formats' => 'jpg,jpeg,png'
        );
    }
    
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->setAttribute('multiple', $options['multiple'])
            ->setAttribute('file_formats', $options['file_formats'])
        ;
    }
    
    public function buildView(FormView $view, FormInterface $form)
    {
        $view
            ->set('multiple', $form->getAttribute('multiple'))
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
