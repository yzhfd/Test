<?php

namespace Magend\IssueBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class IssueType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('year')
            ->add('yearIssueNo')
            ->add('totalIssueNo')
            ->add('title')
            ->add('publishedAt', null, array('required'=>false, 'widget'=>'single_text', 'format' => 'y-MM-dd'))
            ->add('priceLevel', 'choice', array(
                'choices' => array(
                    '0'=>'免费',
                    '1'=>'0.99$/6¥ ',
                    '2'=>'1.99$/12¥ ',
                    '3'=>'2.99$/18¥ ')
            ))
            ->add('iapId', null, array('required' => false, 'read_only' => true))
            ->add('landscapeCoverFile', 'file', array('required'=>false))
            ->add('portraitCoverFile', 'file', array('required'=>false))
            ->add('previewFile', 'file', array('required'=>false))
            ->add('enPreviewFile', 'file', array('required'=>false))
            ->add('audioFile', 'file', array('required'=>false))
            //->add('coverImage', 'file', array('required'=>false))
        ;
    }

    public function getName()
    {
        return 'magend_issuebundle_issuetype';
    }
}
