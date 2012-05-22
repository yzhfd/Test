<?php

namespace Magend\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Magend\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;

class CorpFormType extends AbstractType
{
    private $container = null;
    
    /**
     * Errors are bubbled up for better looking
     * 
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('phone', null, array('error_bubbling' => false))
            ->add('name', null, array('error_bubbling' => false))
            ->add('legalPerson', null, array('error_bubbling' => false))
            ->add('contactId', null, array('error_bubbling' => false))
            ->add('orgCodeFile', 'file', array('error_bubbling' => false))
            ->add('licenseFile', 'file', array('error_bubbling' => false))
            ->add('pledgeFile', 'file', array('error_bubbling' => false))
            ;
    }
    
    public function getDefaultOptions(array $options)
    {
        return array('data_class' => 'Magend\UserBundle\Entity\UserCorp');
    }
    
    public function getName()
    {
        return 'magend_corp';
    }
    
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
}