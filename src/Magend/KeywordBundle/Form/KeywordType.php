<?php

namespace Magend\KeywordBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class KeywordType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('keyword')
        ;
    }
    
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Magend\KeywordBundle\Entity\Keyword',
        );
    }

    public function getName()
    {
        return 'magend_keywordbundle_keywordtype';
    }
}
