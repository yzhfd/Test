<?php

namespace Magend\UserBundle\Controller;

use Exception;
use Magend\BaseBundle\Controller\BaseController as Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Magend\UserBundle\Entity\User;

/**
 * For staff user, under some corp
 * 
 * @Route("/staff/user")
 * @author Kail
 */
class StaffUserController extends Controller
{
    /**
     * @Route("/list", name="staff_user_list")
     * @Template()
     */
    public function listAction()
    {
        $repo = $this->getDoctrine()->getRepository('MagendUserBundle:User');
        $dql = 'SELECT u FROM MagendUserBundle:User u WHERE u.boss IS NOT NULL ORDER BY u.createdAt DESC';
        $em = $this->getDoctrine()->getEntityManager();
        $tplVars = $this->getList('MagendUserBundle:User', $em->createQuery($dql));
        $tplVars['users'] = $tplVars['entities'];
        unset($tplVars['entities']);
        $tplVars['currentUser'] = $this->get('security.context')->getToken()->getUser();
        return $tplVars;
    }
    
    /**
     * @Route("/{id}/del", name="user_del")
     */
    public function delAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('MagendUserBundle:User');
        $user = $repo->find($id);
        $currentUser = $this->get('security.context')->getToken()->getUser();
        if ($user->getBoss() != $currentUser) {
            throw new Exception('staff.not_boss');
        }
        
        $em->remove($user);
        $em->flush();
        return $this->redirect($this->generateUrl('user_list'));
    }
    
    /**
     * Create user under the corporation
     * 
     * @Route("/new", name="staff_user_new")
     * @Template()
     */
    public function newAction()
    {
        $currentUser = $this->get('security.context')->getToken()->getUser();
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
                $user->setBoss($currentUser);
                $um = $this->get('magend.user_manager');
                $um->updateUser($user);
        
                return $this->redirect($this->generateUrl('staff_user_list'));
            }
        }
        
        return array(
            'form' => $form->createView(),
            'currentUser' => $currentUser
        );
    }
    
    /**
     * Show the user
     * 
     * @Route("/show", name="staff_user_show")
     * @Template()
     */
    public function showAction()
    {
        return new Response();
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
