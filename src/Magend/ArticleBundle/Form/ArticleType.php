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
            //->add('audioFile', 'file', array('required'=>false, 'label' => '音频文件'))
            ->add('keywordsText', null, array('required'=>false))
            ->add('lat')
            ->add('lng')
        ;
    }

    public function getName()
    {
        return 'magend_articlebundle_articletype';
    }
}
