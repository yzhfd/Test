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
     * @Route("/{id}/show", name="corp_user_show", requirements={"id"="\d+"})
     * @Template()
     */
    public function showAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendUserBundle:User');
        $user = $repo->find($id);
        return array('user' => $user);
    }

    /**
     * 
     * @Route("/{id}/trial/new", name="corp_trial_new")
     * @Template()
     */
    public function newTrialAction()
    {
        return array();
    }
    
    /**
     * Enable the user
     * 
     * @Route("/{id}/enable", name="corp_user_enable", requirements={"id"="\d+"})
     */
    public function enableAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendUserBundle:User');
        $user = $repo->find($id);
        $user->setEnabled(true);
        $em = $this->getDoctrine()->getEntityManager();
        $em->flush();
        
        return $this->redirect($this->generateUrl('corp_user_show', array('id'=>$id)));
    }
}
