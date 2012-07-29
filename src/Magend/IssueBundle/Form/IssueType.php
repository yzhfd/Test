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
            //->add('coverImage', 'file', array('required'=>false))
        ;
    }

    public function getName()
    {
        return 'magend_issuebundle_issuetype';
    }
}
