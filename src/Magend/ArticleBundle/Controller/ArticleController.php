<?php

namespace Magend\ArticleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Magend\KeywordBundle\Entity\Keyword;

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
        $article = $repo->find(13);
        
        $keyword = 'awesome';
        $kwRepo = $this->getDoctrine()->getRepository('MagendKeywordBundle:Keyword');
        $kwEntity = $kwRepo->findOneBy(array(
            'keyword' => $keyword
        ));
        
        if ($kwEntity == null) {
            $kwEntity = new Keyword($keyword);            
        }
        
        $article->addKeyword($kwEntity);
        $em->persist($article);
        $em->flush();
        
        return array();
    }
}
