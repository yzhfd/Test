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
    
    protected $userManager;
    
    protected $formOptions = array(
        'validation_groups' => 'admin'
    );
    
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->addIdentifier('nickname', null, array('label' => '昵称'))
            ->addIdentifier('email', null, array('label' => '邮箱'))
            /*->add('avatar', null, array('label' => '头像',
                'template' => 'MagendBackendBundle:User:avatar.html.twig'
            ))*/
            ->add('gender', 'trans', array('label' => '性别', 'transIn' => 'gender'))
            ->add('birth', null, array('label' => '出生日期'))
            //->add('lastLogin', null, array('label' => '最近登录'))
            ->add('created_at', null, array('label' => '注册时间'))
            ->add('_action', 'actions', array('label' => '操作',
                'actions' => array(
                    'profile' => array( 'template' => 'MagendBackendBundle:User:profile.html.twig'),
                    'edit'    => array(),
                )
            ))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filterMapper)
    {
        $filterMapper
            ->add('nickname')
            ->add('email', null, array('label' => '注册邮箱'))
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $targetUser = $this->getSubject();
        $sc = $this->container->get('security.context');
        $isSuper = $sc->isGranted('ROLE_SUPER_ADMIN');
        $isSelf = $sc->getToken()->getUser()->getId() == $targetUser->getId();
        if (!$isSuper && !$isSelf && ($targetUser->hasRole('ROLE_CM') || $targetUser->hasRole('ROLE_ADMIN') || $targetUser->hasRole('ROLE_SUPER_ADMIN'))) {
            throw new AccessDeniedHttpException('对不起，您没有相应权限');
        }
        
        $formMapper
            ->with('基本信息')
                
                ->add('nickname', null, array('label' => '昵称'))
                ->add('email', null, array('read_only' => true))
                ->add('plainPassword', 'text', array('required' => false))
                ->add(
                    'gender',
                    'choice',
                    array('label' => '性别', 'choices' => array('0' => '女', '1' => '男'))
                )
                ->add('avatar', null, array('label' => '头像'))
                ->add(
                    'originType',
                    'choice',
                    array('label' => '我是', 'choices' => User::getOriginTypeList())
                )
                ->add('orgMail', null, array('label' => '学校或公司邮箱', 'required' => false))
                ->add('messageQuota', null, array('label' => '通信额度'))
                ->add('fake', null, array('label' => '咱们的人', 'required' => false))
        ->end()
        ->with('用户组')
                ->add('groups', 'sonata_type_model', array('required' => false))
        ->end()
        ->with('管理')
                ->add('locked', null, array('required' => false, 'label' => '封禁'))
                ->add('enabled', null, array('required' => false, 'label' => '激活'))
                       //->add('expired', null, array('required' => false))
                       //->add('credentialsExpired', null, array('required' => false))
                ->add('roles', 'pq_security_roles', array( 'multiple' => true, 'required' => false))
        ->end();
    }

    public function preUpdate($user)
    {
        $this->userManager->updateCanonicalFields($user);
        $this->userManager->updatePassword($user);
    }
    
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

        $actions['rejectAvatar'] = array(
            'label' => $this->trans('头像不合格'),
            'ask_confirmation' => false,
        );

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
        $collection->add('verify', $this->getRouterIdParameter() . '/verify');
        $collection->add('unqualify', $this->getRouterIdParameter() . '/unqualify');
    }
}