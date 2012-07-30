<?php

namespace Magend\IssueBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class IssueType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('landscapeCoverFile', 'file', array('required'=>false))
            ->add('portraitCoverFile', 'file', array('required'=>false))
            ->add('posterFile', 'file', array('required'=>false))
            ->add('audioFile', 'file', array('required'=>false))
            ->add('videoFile', 'file', array('required'=>false))
        ;
    }

    public function getName()
    {
        return 'magend_issuebundle_issuetype';
    }
}
