<?php

namespace Magend\ArticleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Magend\KeywordBundle\Form\KeywordType;
use Magend\ArticleBundle\Entity\Article;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {        
        $builder
            ->add('type', 'choice', array('choices'=>Article::getTypeList(), 'empty_value' => ''))
            ->add('title')
            ->add('enTitle')
        ;
    }

    public function getName()
    {
        return 'magend_articlebundle_articletype';
    }
}
