<?php

namespace Magend\HotBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class HotController extends Controller
{
    /**
     * @Route("/hot")
     * @Template()
     */
    public function indexAction()
    {
        return array('name' => 'Kail');
    }
}
