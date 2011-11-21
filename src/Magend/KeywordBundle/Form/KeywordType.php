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
            ->add('createdAt')
            ->add('articles')
        ;
    }

    public function getName()
    {
        return 'magend_keywordbundle_keywordtype';
    }
}
