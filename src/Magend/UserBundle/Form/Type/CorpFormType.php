<?php

namespace Magend\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Magend\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;

class CorpFormType extends AbstractType
{
    private $container = null;
    
    private $isRegister = true;
    
    public function __construct($isRegister = true)
    {
        $this->isRegister = $isRegister;
    }
    
    /**
     * Errors are bubbled up for better looking
     * 
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('phone', null, array('error_bubbling' => false, 'label' => '电话'))
            ->add('name', null, array('error_bubbling' => false, 'label' => '企业名称'))
            ->add('legalPerson', null, array('error_bubbling' => false, 'label' => '法人代表'))
            ->add('contactId', null, array('error_bubbling' => false, 'label' => '联系人身份证'))
            ->add('orgCodeFile', 'file', array('error_bubbling' => false, 'label' => '组织机构代码', 'required' => $this->isRegister))
            ->add('licenseFile', 'file', array('error_bubbling' => false, 'label' => '营业执照', 'required' => $this->isRegister))
            ->add('pledgeFile', 'file', array('error_bubbling' => false, 'label' => '安全承诺书', 'required' => $this->isRegister))
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