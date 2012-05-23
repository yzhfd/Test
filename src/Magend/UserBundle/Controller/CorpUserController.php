<?php

namespace Magend\UserBundle\Controller;

use Magend\BaseBundle\Controller\BaseController as Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * 
 * For admin
 * 
 * @Route("/admin/corp/user")
 * @author Kail
 */
class CorpUserController extends Controller
{
    /**
     * @Route("/list", name="corp_user_list")
     * @Template()
     */
    public function listAction()
    {
        $repo = $this->getDoctrine()->getRepository('MagendUserBundle:User');
        $dql = 'SELECT u FROM MagendUserBundle:User u WHERE u.corp IS NOT NULL ORDER BY u.createdAt DESC';
        $em = $this->getDoctrine()->getEntityManager();
        $arr = $this->getList('MagendUserBundle:User', $em->createQuery($dql));
        $arr['users'] = $arr['entities'];
        unset($arr['entities']);
        return $arr;
    }
    
    /**
     * 
     * @Route("/{id}/show", name="corp_user_show", requirements={"id" = "\d+"})
     */
    public function showAction($id)
    {
        return array();
    }
    
    /**
     * @Route("/{id}/del", name="corp_user_del")
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
