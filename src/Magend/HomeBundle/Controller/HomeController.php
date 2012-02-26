<?php

namespace Magend\HomeBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * 
 * @author Kail
 */
class HomeController extends Controller
{
    /**
     * @Route("/dashboard", name="home")
     * @Template()
     */
    public function indexAction()
    {
        $req = $this->getRequest();
        $magId = $req->cookies->get('magzine_id');
        $magzine = null;
        if ($magId !== null) {
            $repo = $this->getDoctrine()->getRepository('MagendMagzineBundle:Magzine');
            $magzine = $repo->find($magId);
        }
        
        
        return array(
            'magzine' => $magzine
        );
    }
}
