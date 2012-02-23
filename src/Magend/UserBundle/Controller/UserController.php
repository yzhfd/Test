<?php

namespace Magend\UserBundle\Controller;

use Magend\BaseBundle\Controller\BaseController as Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * 
 * @Route("/user")
 * @author Kail
 */
class UserController extends Controller
{
    /**
     * @Route("/hello/{name}")
     * @Template()
     */
    public function indexAction($name)
    {
        return array('name' => $name);
    }
    
    /**
     * RESTful login
     * 
     */
    public function restfulLoginAction()
    {
        // username
        // plainPassword
    }

    /**
     * @Route("/list", name="user_list")
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
