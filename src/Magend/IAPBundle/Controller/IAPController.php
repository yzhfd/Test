<?php

namespace Magend\IAPBundle\Controller;

use DateTime;
use Magend\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * IAP controller
 * 
 * @Route("/iap")
 * @author kail
 */
class IAPController extends Controller
{
    /**
     * 
     * @Route("/list", name="iap_list")
     * @Template()
     */
    public function listAction()
    {
        $arr = $this->getList('MagendIAPBundle:IAP');
        $arr['iaps'] = $arr['entities'];
        unset($arr['entities']);
        return $arr;
    }
    
    /**
     * 
     * @Route("/{id}/distribute", name="iap_distribute", requirements={"id"="\d+"})
     * @Template()
     */
    public function distributeAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendIAPBundle:IAP');
        $iap = $repo->find($id);
        
        $formBuilder = $this->createFormBuilder($iap->getIssue());
        $form = $formBuilder->add('iapId', null, array('label' => 'IAP ID'))
                            ->getForm();
        
        $req = $this->getRequest();
        if ($req->getMethod() == 'POST') {
            $form->bindRequest($req);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $iap->setDistributedAt(new DateTime());
                $em->flush();
                
                return $this->redirect($this->generateUrl('iap_list'));
            }
        }
        
        return array(
            'form' => $form->createView()
        );
    }
}