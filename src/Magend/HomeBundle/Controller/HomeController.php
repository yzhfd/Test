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
        // if from ios
        // then return $this->container->get('templating')->renderResponse('MagendUserBundle:User:user.xml.twig');
        return array();
    }
}
