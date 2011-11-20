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
        
        try {
            // ...
            $em->flush();
        } catch( \PDOException $e ) {
            if( $e->getCode() === '23000' )
            {
                echo $e->getMessage();

            // Will output an SQLSTATE[23000] message, similar to:
            // Integrity constraint violation: 1062 Duplicate entry 'x'
            // ... for key 'UNIQ_BB4A8E30E7927C74'
            } else throw $e;
        }
        
        return array();
    }
}
