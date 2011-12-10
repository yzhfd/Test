<?php

namespace Magend\ArticleBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Magend\ArticleBundle\Entity\Article;
use Magend\PageBundle\Entity\Page;
use Magend\IssueBundle\Entity\Issue;
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
            if (!isset($page['id'])) {
                throw new \ Exception('Page need be persisted first');
            }
            
            $pageEntity = null;
            $pageEntity = $pageRepo->find($page['id']);
            unset($page['id']);
            if (empty($pageEntity)) {
                $pageEntity = new Page();
            }
            
            // no hots
            foreach ($page as $key=>$val) {
                if (method_exists($pageEntity, "set$key")) {
                    $method = "set$key";
                    $pageEntity->$method($val);
                }
            }
            
            // necessary for bidirectional associations
            $pageEntity->setArticle($article);
            $pageEntites[] = $pageEntity;
        }
        
        $article->setPages($pageEntites);
        $em->persist($article);
        $em->flush();
        
        return new Response('');
    }
    
    /**
     * 
     * @Route("/orderpages", name="article_orderpages", defaults={"_format" = "json"})
     */
    public function orderPagesAction()
    {
        $req = $this->getRequest();
        $articleId = $req->get('id');
        $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
        $article = $repo->find($articleId);
        if (!$article || !$req->get('pageIds')) {
            return new Response(json_encode(array('result'=>0)));
        }
        $pageIds = $req->get('pageIds');
        $article->setPageIds($pageIds);
        
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($article);
        $em->flush();
        
        return new Response('');
    }

    /**
     * 
     * @Route("/{id}/edit", name="article_edit", requirements={"id" = "\d+"})
     * @Template("MagendArticleBundle:Article:new.html.twig")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
        $article = $repo->find($id);
        if (empty($article)) {
            throw new \ Exception('Article not found');
        }
        
        $kwRepo = $this->getDoctrine()->getRepository('MagendKeywordBundle:Keyword');
        $kws = $kwRepo->findAll();
        $atRepo = $this->getDoctrine()->getRepository('MagendArchitectBundle:Architect');
        $ats = $atRepo->findAll();
        
        $req  = $this->getRequest();
        $form = $this->createForm(new ArticleType(), $article);
        
        if ($req->getMethod() == 'POST') {
            $form->bindRequest($req);
            if ($form->isValid()) {
                $kwText = trim($article->getKeywordsText());
                if (!empty($kwText)) {
                    $keywords = $kwRepo->toEntities(explode(',', $kwText));
                    $article->setKeywords($keywords);
                }
                
                $atText = trim($article->getArchitectsText());
                if (!empty($atText)) {
                    $architects = $atRepo->toEntities(explode(',', $atText));
                    $article->setArchitects($architects);
                }
                
                $em->persist($article);
                $em->flush();
                
                return $this->redirect($this->generateUrl('article_show', array('id' => $article->getId())));
            }
        }
        
        $issue = $article->getIssue();
        $issue->getId();
        return array(
            'architects' => $ats,
            'keywords' => $kws,
            'issue'   => $issue,
            'article' => $article,
            'form'    => $form->createView()
        );
    }
    
    /**
     * 
     * @Route("/issue_{id}/new", name="article_new", requirements={"id" = "\d+"})
     * @Template()
     */
    public function newAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
        $issue = $repo->find($id);
        if (empty($issue)) {
            throw new \ Exception('Issue not found');
        }
        
        $article = new Article();
        
        $kwRepo = $this->getDoctrine()->getRepository('MagendKeywordBundle:Keyword');
        $kws = $kwRepo->findAll();
        $atRepo = $this->getDoctrine()->getRepository('MagendArchitectBundle:Architect');
        $ats = $atRepo->findAll();
                
        $req  = $this->getRequest();
        $form = $this->createForm(new ArticleType(), $article);
        
        if ($req->getMethod() == 'POST') {
            $form->bindRequest($req);
            if ($form->isValid()) {
                $kwText = trim($article->getKeywordsText());
                if (!empty($kwText)) {
                    $keywords = $kwRepo->toEntities(explode(',', $kwText));
                    $article->setKeywords($keywords);
                }
                
                $atText = trim($article->getArchitectsText());
                if (!empty($atText)) {
                    $architects = $atRepo->toEntities(explode(',', $atText));
                    $article->setArchitects($architects);
                }
                
                $issue->addArticle($article);
                $article->setIssue($issue);
                $em->persist($issue);
                $em->persist($article);
                $em->flush();
                
                return $this->redirect($this->generateUrl('article_show', array('id' => $article->getId())));
            }
        }
        
        return array(
            'architects' => $ats,
            'keywords' => $kws,
            'issue'   => $issue,
            'article' => $article,
            'form'    => $form->createView()
        );
    }

    /**
     * @Route("/{id}", name="article_update", requirements={"id" = "\d+"})
     * @Method("post")
     * @Template()
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
        $article = $repo->find($id);
        
        if ($article) {
            // @todo update
        }
        return new Response('');
    }
    
    /**
     * @Route("/{id}", name="article_show", requirements={"id" = "\d+"})
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        if ($this->getRequest()->isXmlHTTPRequest()) {
            $query = $em->createQuery('SELECT partial a.{id, title, pageIds}, partial p.{id, landscapeImg, portraitImg, label} FROM MagendArticleBundle:Article a LEFT JOIN a.pages p WHERE a.id = :articleId')
                        ->setParameter('articleId', $id);
        
            $arr = $query->getArrayResult();
            $article = array_pop($arr);
            $article['pages'] = array_values($article['pages']);
            $response = new Response(json_encode($article));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else {
            $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
            $article = $repo->find($id);
        }
        
        return array(
            'article' => $article
        );
    }
    
    /**
     * 
     * New the article, just itself
     * 
     * Must be put below /{id}, or 301 to this
     * and all requests will be GET
     * 
     * @Route("", name="article_new_update", defaults={"_format" = "json"})
     */
    public function newUpdateAction()
    {
        $req = $this->getRequest();
        $json = $req->getContent();
        $paramsObj = json_decode($json);
        $em = $this->getDoctrine()->getEntityManager();
        if (isset($paramsObj->id)) {
            $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
            $article = $repo->find($paramsObj->id);
            if (empty($article)) {
                throw new \ Exception('article ' . $paramsObj->id . ' not found');
            }
        } else {
            // @todo might not need issue id
            if (!isset($paramsObj->issueId)) {
                return new Response(json_encode(array(
                    'error' => 'Issue Id is required'
                )));
            }
            
            $issueId = $paramsObj->issueId;
            $article = new Article();
            $issueRef = $em->getReference('MagendIssueBundle:Issue', $issueId);
            $issueRef->addArticle($article);
            $article->setIssue($issueRef);
        }

        if (isset($paramsObj->title)) {
            $article->setTitle($paramsObj->title);
        }
        
        if (isset($paramsObj->pageIds)) {
            $pageIds = $paramsObj->pageIds;
            $article->setPageIds($pageIds);
            // did associate in page controller
            /*
            $pageRefs = array();
            foreach ($pageIds as $pageId) {
                $pageRef = $em->getReference('MagendPageBundle:Page', $pageId);
                $pageRef->setArticle($article);
                $pageRefs[] = $pageRef;
            }
            
            $article->setPages($pageRefs);
            $article->setPageIds($pageIds);*/
        }
        
        $em->persist($article);
        $em->flush();
        
        if (isset($paramsObj->id)) {
            $response = '{}';
        } else {
            $response = json_encode(array(
                'id' => $article->getId()
            ));
        }
        return new Response($response);
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
