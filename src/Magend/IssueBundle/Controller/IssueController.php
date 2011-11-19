<?php

namespace Magend\IssueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * 
 * @Route("/issue")
 * @author kail
 */
class IssueController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        $repo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
        $issues = $repo->findAll();
        
        $articles = $issues[0]->getArticles();
        $name = $articles[0]->getTitle();
        
        return array('name' => $name);
    }
}
