<?php

namespace Magend\ArticleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Magend\KeywordBundle\Form\KeywordType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $types = array();
        // article types
        // @todo better from Article
        for ($i=0; $i<2; ++$i) {
            $types[] = "article.$i";
        }
        
        $builder
            ->add('type', 'choice', array('choices'=>$types, 'empty_value' => ''))
            ->add('title')
            ->add('enTitle')
        ;
    }

    public function getName()
    {
        return 'magend_articlebundle_articletype';
    }
}
