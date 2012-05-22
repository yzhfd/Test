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
use Magend\UserBundle\Entity\User;

class UserAdmin extends Admin
{
    /**
     * Service container
     * 
     * @var Container
     */
    protected $container;
    
    protected $datagridValues = array(
        '_page'       => 1,
        '_sort_order' => 'DESC', // sort direction
    );
    
    protected $userManager;
    
    protected $formOptions = array(
        'validation_groups' => 'admin'
    );
    
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->addIdentifier('username', null, array('label' => '用户ID'))
            ->addIdentifier('email', null, array('label' => '邮箱'))
            /*->add('avatar', null, array('label' => '头像',
                'template' => 'MagendBackendBundle:User:avatar.html.twig'
            ))*/
            //->add('lastLogin', 'datetime', array('label' => '最近登录'))
            ->add('created_at', 'datetime', array('label' => '注册时间'))
            ->add('corp', null, array('label' => '企业'))
            ->add('enabled', 'boolean', array('label' => '激活', 'editable' => true))
            ->add('_action', 'actions', array('label' => '操作',
                'actions' => array(
                    'edit' => array(),
                    'corp' => array('template' => 'MagendBackendBundle:User:corp.html.twig'),
                )
            ))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filterMapper)
    {
        $filterMapper
            ->add('username')
            ->add('email', null, array('label' => '注册邮箱'))
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $targetUser = $this->getSubject();
        if ($targetUser === null) {
            $targetUser->setEnabled(true);
        }
        
        /*
        $sc = $this->container->get('security.context');
        $isSuper = $sc->isGranted('ROLE_SUPER_ADMIN');
        $isSelf = $sc->getToken()->getUser()->getId() == $targetUser->getId();
        if (!$isSuper && !$isSelf && ($targetUser->hasRole('ROLE_CM') || $targetUser->hasRole('ROLE_ADMIN') || $targetUser->hasRole('ROLE_SUPER_ADMIN'))) {
            throw new AccessDeniedHttpException('对不起，您没有相应权限');
        }
        */
        
        $formMapper
            ->with('基本信息')
                ->add('username', null, array('label' => '用户ID'))
                ->add('email', null, array('read_only' => $targetUser->getId() != null))
                ->add('plainPassword', 'text', array('required' => false, 'label' => '密码'))
                //->add('avatar', null, array('label' => '头像'))
        ->end()
        ->with('管理')
                ->add('locked', null, array('required' => false, 'label' => '封禁'))
                ->add('enabled', null, array('required' => false, 'label' => '激活'))
                ->add('roles', 'magend_security_roles', array( 'multiple' => true, 'required' => false))
        ->end();
    }

    /*
    public function preUpdate($user)
    {
        $this->userManager->updateCanonicalFields($user);
        $this->userManager->updatePassword($user);
    }
    */
    
    /**
     * Set UserManager
     * 
     * @param UserManagerInterface $userManager
     */
    public function setUserManager(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
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
    
    protected function configureSideMenu(MenuItemInterface $menu, $action, Admin $childAdmin = null)
    {
        if (!$childAdmin && !in_array($action, array('edit'))) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;
        $id = $admin->getRequest()->get('id');
        $menu->addChild(
            $this->trans('edit_user'),
            array('uri' => $admin->generateUrl('edit', array('id' => $id)))
        );
    }
    
    /**
     * @return array
     */
    public function getBatchActions()
    {
        $actions = parent::getBatchActions();

        /*
        $actions['rejectAvatar'] = array(
            'label' => $this->trans('头像不合格'),
            'ask_confirmation' => false,
        );*/

        $actions['disabled'] = array(
            'label' => $this->trans('batch_disable_comments'),
            'ask_confirmation' => false
        );

        return $actions;
    }
    
    public function rejectAvatar($user)
    {
        die('your avatar is shit');
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        // @todo add notify
        
        // $collection->add('verify', $this->getRouterIdParameter() . '/notify');
    }
}