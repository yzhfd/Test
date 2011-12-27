<?php

namespace Magend\FeedbackBundle\Controller;

use Magend\BaseBundle\Controller\BaseController as Controller;
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
        $arr = $this->getList('MagendFeedbackBundle:Feedback');
        $arr['feedbacks'] = $arr['entities'];
        unset($arr['entities']);
        return $arr;
    }
}
