<?php

namespace Magend\ArticleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Magend\KeywordBundle\Form\KeywordType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('keywordsText')
            ->add('architectsText')
        ;
    }

    public function getName()
    {
        return 'magend_articlebundle_articletype';
    }
}
