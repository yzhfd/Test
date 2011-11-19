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
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
        $issues = $repo->findAll();
        
        $articles = $issues[0]->getArticles();
        $name = $articles[0]->getTitle();
        
        //$em->remove($issues[0]);
        //$em->flush();
        
        return array('name' => $name);
    }
}
