<?php

namespace Magend\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Magend\UserBundle\Entity\User;

class RegistrationFormType extends BaseType
{
    private $container = null;
    
    /**
     * Errors are bubbled up for better looking
     * 
     * (non-PHPdoc)
     * @see Form/Type/FOS\UserBundle\Form\Type.RegistrationFormType::buildForm()
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('username', null, array('error_bubbling' => false))
            ->add('nickname', null, array('error_bubbling' => false))
            ->add('email', 'email', array('error_bubbling' => false))
            ->add('plainPassword', 'repeated', array('type' => 'password', 'invalid_message' => 'password.unmatched', 'error_bubbling' => false))
            ;
    }
    
    public function getDefaultOptions(array $options)
    {
        return array(
            'validation_groups' => array('Registration'),
            'csrf_protection' => false,
        );
    }

    public function getName()
    {
        return 'magend_user_registration';
    }
    
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
}