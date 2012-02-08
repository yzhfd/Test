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
        for ($i=0; $i<7; ++$i) {
            $types[] = "article.$i";
        }
        
        $builder
            ->add('type', 'choice', array('choices'=>$types, 'empty_value' => ''))
            ->add('title')
            //->add('audioFile', 'file', array('required'=>false, 'label' => '音频文件'))
            ->add('keywordsText', null, array('required'=>false))
            ->add('project', 'entity', array(
                'required' => false,
                'class' => 'MagendProjectBundle:Project'
            ))
            ->add('institutes', 'entity', array(
                'required' => false,
                'class' => 'MagendInstituteBundle:Institute',
                'expanded' => false,
                'multiple' => true
            ))
            ->add('lat')
            ->add('lng')
        ;
    }

    public function getName()
    {
        return 'magend_articlebundle_articletype';
    }
}
