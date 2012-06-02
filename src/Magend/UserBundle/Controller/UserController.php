<?php

namespace Magend\UserBundle\Controller;

use Magend\BaseBundle\Controller\BaseController as Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Magend\UserBundle\Entity\User;

/**
 * 
 * For corp user or staff user
 * 
 * @Route("/user")
 * @author Kail
 */
class UserController extends Controller
{   
    /**
     *
     * @Route("/edit", name="user_edit")
     * @Template()
     */
    public function editAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $formBuilder = $this->createFormBuilder($user);
        $form = $formBuilder->add('nickname', null, array('label' => '昵称'))
                            ->add('email', null, array('label' => '邮箱'))
                            ->add('mobile', null, array('label' => '手机'))
                            ->add('plainPassword', 'repeated', array('type' => 'password', 'required' => false))
                            ->getForm();
        $req = $this->getRequest();
        if ($req->getMethod() == 'POST') {
            $form->bindRequest($req);
            if ($form->isValid()) {
                $um = $this->get('magend.user_manager');
                $um->updateUser($user);
    
                return $this->redirect($this->generateUrl('user_edit'));
            }
        }
    
        return array(
                'form' => $form->createView()
        );
    }
}
