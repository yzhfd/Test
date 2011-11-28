<?php

namespace Magend\ArticleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Magend\ArticleBundle\Entity\Article;
use Magend\PageBundle\Entity\Page;
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
     * // , requirements={"id" = "\d+"}, defaults={"id" = null}
     * 
     * @Route("/new_pages", name="article_new_pages")
     * @Template()
     */
    public function newPagesAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $req = $this->getRequest();
        $articleId = $req->get('id');
        if (!empty($articleId)) {
            $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
            $article = $repo->find($articleId);
        } else {
            $article = new Article();
        }
        
        $pageRepo = $this->getDoctrine()->getRepository('MagendPageBundle:Page');
        $pages = $req->get('pages');
        $pageEntites = array();
        foreach ($pages as $page) {
            $pageEntity = null;
            if (isset($page['id'])) {
                $pageEntity = $pageRepo->find($page['id']);
                unset($page['id']);
            }
            if (empty($pageEntity)) {
                $pageEntity = new Page();
            }
            
            foreach ($page as $key=>$val) {
                if (method_exists($pageEntity, "set$key")) {
                    $method = "set$key";
                    $pageEntity->$method($val);
                }
            }
            
            $pageEntites[] = $pageEntity;
        }
        
        $article->setPages($pageEntites);
        $em->persist($article);
        $em->flush();
        
        print_r($article->getId()); exit;
    }
    
    /**
     * 
     * @Route("/new", name="article_new")
     * @Template()
     */
    public function newAction()
    {
        $article = new Article();
        $article->addKeyword(new Keyword('mmml'));
        $article->addKeyword(new Keyword('koolll'));
        $req  = $this->getRequest();
        $form = $this->createForm(new ArticleType(), $article);
        
        if ($req->getMethod() == 'POST') {
            $form->bindRequest($req);
            if ($form->isValid()) {
                $kwText = trim($article->getKeywordsText());
                if (!empty($kwText)) {
                    $kwRepo = $this->getDoctrine()->getRepository('MagendKeywordBundle:Keyword');
                    $keywords = $kwRepo->toEntities(explode(',', $kwText));
                    $article->setKeywords($keywords);
                }
                
                $atText = trim($article->getArchitectsText());
                if (!empty($atText)) {
                    $atRepo = $this->getDoctrine()->getRepository('MagendArchitectBundle:Architect');
                    $architects = $atRepo->toEntities(explode(',', $atText));
                    $article->setArchitects($architects);
                }
        
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
