<?php

namespace Magend\ArticleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Magend\ArticleBundle\Entity\Article;
use Magend\ArticleBundle\Form\ArticleType;
use Magend\KeywordBundle\Entity\Keyword;

/**
 * 
 * @Route("/article")
 * @author kail
 */
class ArticleController extends Controller
{
    /**
     * 
     * @Route("/new", name="article_new")
     * @Template()
     */
    public function newAction()
    {
        $article = new Article();
        $req   = $this->getRequest();
        $form  = $this->createForm(new ArticleType(), $article);
        
        if ($req->getMethod() == 'POST') {
            $form->bindRequest($req);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($article);
                $em->flush();
                
                return $this->redirect($this->generateUrl('article_show', array('id' => $article->getId())));
            }
        }
        
        return array(
            'article' => $article,
            'form'    => $form->createView()
        );
    }
    
    /**
     * @Route("/show/{id}", name="article_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
        $article = $repo->find($id);
        
        return array(
            'article' => $article
        );
    }
    
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
