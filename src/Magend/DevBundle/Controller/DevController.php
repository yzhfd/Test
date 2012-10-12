<?php

namespace Magend\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * 
 * @Route("/dev")
 * @author kail
 */
class DevController extends Controller
{
    /**
     * @Route("/", name="dev_home")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
}
