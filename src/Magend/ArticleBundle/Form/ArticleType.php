<?php

namespace Magend\ArticleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('keywords', 'text')
            ->add('architects', 'text')
        ;
    }

    public function getName()
    {
        return 'magend_articlebundle_articletype';
    }
}
