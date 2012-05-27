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
     * @Route("/{id}/del", name="staff_user_del")
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
        return $this->redirect($this->generateUrl('staff_user_list'));
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
        $um = $this->get('magend.user_manager');
        $user = $um->createUser();
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
                $user->addRole('ROLE_STAFF');
                $user->setBoss($currentUser);
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
     * bind(unbind), bind users with mags
     * 
     * @Route("/{id}/bind-mags", name="staff_user_bind", requirements={"id"="\d+"})
     * @Template()
     */
    public function bindAction($id)
    {
        $currentUser = $this->get('security.context')->getToken()->getUser();
        $dql = 'SELECT m FROM MagendMagzineBundle:Magzine m WHERE m.owner = :user';
        $em = $this->getDoctrine()->getEntityManager();
        $q = $em->createQuery($dql)->setParameter('user', $currentUser);
        $mags = $q->getResult();
        
        return array(
            'mags' => $mags
        );
    }
    
    /**
     * 
     * @Route("/{id}/edit", name="staff_user_edit", requirements={"id"="\d+"})
     * @Template("MagendUserBundle:StaffUser:new.html.twig")
     */
    public function editAction($id)
    {
        $um = $this->get('magend.user_manager');
        $repo = $this->getDoctrine()->getRepository('MagendUserBundle:User');
        $user = $repo->find($id);
        $formBuilder = $this->createFormBuilder($user);
        $form = $formBuilder->add('username', null, array('label' => 'ID'))
                            ->add('email', null, array('label' => '邮箱'))
                            ->add('nickname', null, array('label' => '昵称'))
                            ->add('plainPassword', 'repeated', array('type' => 'password', 'required' => false))
                            ->getForm();
        $req = $this->getRequest();
        if ($req->getMethod() == 'POST') {
            $form->bindRequest($req);
            if ($form->isValid()) {
                $um->updateUser($user);
        
                return $this->redirect($this->generateUrl('staff_user_list'));
            }
        }
        
        return array(
            'form' => $form->createView()
        );
    }
}
