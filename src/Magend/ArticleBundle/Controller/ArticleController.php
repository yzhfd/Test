<?php

namespace Magend\ArticleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * 
 * @Route("/article")
 * @author kail
 */
class ArticleController extends Controller
{
    /**
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
        $articles = $repo->findAll();
        
        
        
        $artciles->getPages();
        // $pages = $article->getPages();
        // echo count($pages);exit;
        
        //$em->remove($article);
        //$em->flush();
        
        return array();
    }
}
