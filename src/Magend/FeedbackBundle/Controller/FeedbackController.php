<?php

namespace Magend\FeedbackBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * 
 * @Route("/feedback")
 * @author Kail
 */
class FeedbackController extends Controller
{
    /**
     * @Route("/list", name="feedback_list")
     * @Template()
     */
    public function listAction()
    {
        
        return array();
    }
}
