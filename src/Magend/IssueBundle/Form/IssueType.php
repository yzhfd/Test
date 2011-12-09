<?php

namespace Magend\IssueBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class IssueType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('serial')
            ->add('title')
            ->add('priceLevel', 'choice', array(
                'choices' => array('0'=>'免费', '1'=>'1', '2'=>'2', '3'=>'3')
            ))
            ->add('coverImage', 'file', array('required'=>false))
        ;
    }

    public function getName()
    {
        return 'magend_issuebundle_issuetype';
    }
}
