<?php

namespace Magend\BackendBundle\Admin\Entity;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use FOS\UserBundle\Model\UserManagerInterface;
use Magend\ArticleBundle\Entity\Category;

class CategoryAdmin extends Admin
{
    /**
     * Service container
     * 
     * @var Container
     */
    protected $container;
    
    protected $datagridValues = array(
        '_page'       => 1
    );
    
    protected $formOptions = array(
        'validation_groups' => 'admin'
    );
    
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            // ->addIdentifier('id')
            ->addIdentifier('magazine', null, array('label' => '杂志'))
            ->addIdentifier('title', null, array('label' => '标题'))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filterMapper)
    {
        $filterMapper
            ->add('magazine', null, array('label' => '杂志'))
            ->add('title', null, array('label' => '标题'))
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('magazine', null, array('label' => '杂志'))
            ->add('title', null, array('label' => '标题'))
        ->end();
    }
    
    /**
     * Set Container
     * 
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
}