<?php

namespace Magend\UserBundle\Controller;

use Magend\BaseBundle\Controller\BaseController as Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Magend\UserBundle\Entity\User;

/**
 * For admin and corp user
 * 
 * @Route("/inner/user")
 * @author Kail
 */
class InnerUserController extends Controller
{
    /**
     * @Route("/list", name="inner_user_list")
     * @Template()
     */
    public function listAction()
    {
        $repo = $this->getDoctrine()->getRepository('MagendUserBundle:User');
        $arr = $this->getList('MagendUserBundle:User');
        $arr['users'] = $arr['entities'];
        unset($arr['entities']);
        return $arr;
    }
    
    /**
     * @Route("/{id}/del", name="user_del")
     */
    public function delAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('MagendUserBundle:User');
        $user = $repo->find($id);
        
        $em->remove($user);
        $em->flush();
        return $this->redirect($this->generateUrl('user_list'));
    }
    
    /**
     * Create user under the corporation
     * 
     * @Route("/new", name="inner_user_new")
     * @Template()
     */
    public function newAction()
    {
        $user = new User();
        $formBuilder = $this->createFormBuilder($user);
        $form = $formBuilder->add('username', null, array('label' => 'ID'))
                            ->add('email', null, array('label' => '邮箱'))
                            ->add('nickname', null, array('label' => '昵称'))
                            ->add('plainPassword', 'repeated', array('type' => 'password'))
                            ->getForm();
        $req = $this->getRequest();
        if ($req->getMethod() == 'POST') {
            $form->bindRequest($req);
            if ($form->isValid()) {
                $user->setEnabled(true);
                $um = $this->get('magend.user_manager');
                $um->updateUser($user);
        
                return $this->redirect($this->generateUrl('inner_user_list'));
            }
        }
        return array(
                'form' => $form->createView()
        );
    }
    
    /**
     * User edits its profile(not administration)
     * 
     * @Route("/edit", name="user_edit")
     */
    public function editAction()
    {
        // @todo ROLE_ADMIN and $req->getParameter('id')
        $user = $this->get('security.context')->getToken()->getUser();
        $formBuilder = $this->createFormBuilder($user);
        /*$form = $formBuilder->add('mobile', null, array('label' => '手机号'))
                            ->getForm();*/
    }
}
