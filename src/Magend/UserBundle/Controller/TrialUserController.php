<?php

namespace Magend\UserBundle\Controller;

use Magend\BaseBundle\Controller\BaseController as Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Magend\UserBundle\Entity\User;
use Magend\UserBundle\Form\Type\CorpFormType;

/**
 * 
 * For admin
 * 
 * @Route("/admin/trial/user")
 * @author Kail
 */
class TrialUserController extends Controller
{
    /**
     * @Route("/list", name="trial_user_list")
     * @Template()
     */
    public function listAction()
    {
        $repo = $this->getDoctrine()->getRepository('MagendUserBundle:User');
        $dql = 'SELECT u FROM MagendUserBundle:User u LEFT JOIN u.corp cp WHERE cp.trial = true ORDER BY u.createdAt DESC';
        $em = $this->getDoctrine()->getEntityManager();
        $arr = $this->getList('MagendUserBundle:User', $em->createQuery($dql));
        $arr['users'] = $arr['entities'];
        unset($arr['entities']);
        return $arr;
    }
    
    /**
     * 
     * @Route("/{id}/show", name="trial_user_show", requirements={"id"="\d+"})
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
     * @Route("/new", name="trial_user_new")
     * @Template()
     */
    public function newAction()
    {
        $um = $this->get('magend.user_manager');
        $user = $um->createUser();
        $formBuilder = $this->createFormBuilder($user);
        $form = $formBuilder->add('username', null, array('label' => 'ID'))
                            ->add('email', null, array('label' => '邮箱'))
                            ->add('nickname', null, array('label' => '昵称'))
                            ->add('plainPassword', 'repeated', array('type' => 'password'))
                            ->add('corp', new CorpFormType(false))
                            ->getForm();
        $req = $this->getRequest();
        if ($req->getMethod() == 'POST') {
            $form->bindRequest($req);
            if ($form->isValid()) {
                $corp = $user->getCorp();
                $corp->setName('imagshow试用');
                $corp->setLegalPerson('imagshow试用');
                $corp->setContactId('201212345678');
                $corp->setTrial(true);
                $user->setEnabled(true);
                $user->addRole('ROLE_CORP');
                $um = $this->get('magend.user_manager');
                $um->updateUser($user);
                
                return $this->redirect($this->generateUrl('trial_user_list'));
            }
        }
        return array(
            'form' => $form->createView()
        );
    }
    
    /**
     * @Route("/{id}/del", name="trial_user_del", requirements={"id"="\d+"})
     */
    public function delAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('MagendUserBundle:User');
        $user = $repo->find($id);
    
        $em->remove($user);
        $em->flush();
        return $this->redirect($this->generateUrl('trial_user_list'));
    }
}
