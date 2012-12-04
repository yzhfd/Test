<?php

namespace Magend\ArticleBundle\Controller;

use Exception;
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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Magend\OperationBundle\Entity\Operation;

/**
 * 
 * @Route("/article")
 * @author kail
 */
class ArticleController extends Controller
{
    /**
     * 
     * @Route("/fix-nbpages", name="article_fix_nbpages")
     */
    public function fixNbPagesAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $dql = 'SELECT COUNT(p.id) FROM MagendArticleBundle:Article a LEFT JOIN a.pages p WHERE a.id = :article';
        $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
        $pageRepo = $this->getDoctrine()->getRepository('MagendPageBundle:Page');
        $articles = $repo->findAll();
        foreach ($articles as $article) {
            $q = $em->createQuery($dql)->setParameter('article', $article->getId());
            $nbPages = $q->getSingleScalarResult();
            $article->setNbPages($nbPages);
        }
        
        $em->flush();
        die('done');
    }
    
    /**
     * 
     * 
     * @Route("/seq-pages", name="article_seq_pages") 
     */
    public function seqPagesAction()
    {
        $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
        $pageRepo = $this->getDoctrine()->getRepository('MagendPageBundle:Page');
        $articles = $repo->findAll();
        foreach ($articles as $article) {
            $pageIds = explode(',', $article->pageIds);
            if (count($pageIds) > 1) {
                $index = 0;
                foreach ($pageIds as $pageId) {
                    if (empty($pageId)) continue;
                    $page = $pageRepo->find($pageId);
                    if (empty($page)) continue;
                    $page->setSeq($index);
                    ++$index;
                }
            }
            
            $pageIds = explode(',', $article->infoPageIds);
            if (count($pageIds) > 1) {
                $index = 0;
                foreach ($pageIds as $pageId) {
                    if (empty($pageId)) continue;
                    $page = $pageRepo->find($pageId);
                    if (empty($page)) continue;
                    $page->setSeq($index);
                    ++$index;
                }
            }
            
            $pageIds = explode(',', $article->structurePageIds);
            if (count($pageIds) > 1) {
                $index = 0;
                foreach ($pageIds as $pageId) {
                    if (empty($pageId)) continue;
                    $page = $pageRepo->find($pageId);
                    if (empty($page)) continue;
                    $page->setSeq($index);
                    ++$index;
                }
            }
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        $em->flush();
        
        die('done');
    }
    
    private function getArticleById($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
        $article = $repo->find($id);
        if (empty($article)) {
            throw new Exception('Article not found');
        }
        
        return $article;
    }
    
    /**
     * Clone the article
     * 
     * @Route("/{id}/clone", name="article_clone", requirements={"id" = "\d+"}) 
     */
    public function cloneAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $article = $this->getArticleById($id);
        $cloneArticle = clone $article;
        $cloneArticle->setId(null);
        $cloneArticle->clonePages();
        $em->persist($cloneArticle);
        $article->getIssue()->addArticle($cloneArticle);
        $em->flush();
        
        return $this->redirect($this->generateUrl('issue_article_list', array('id' => $article->getIssue()->getId())));
    }
    
    /**
     * Set the article as copyright
     * 
     * @Route("/{id}/copyright", name="article_copyright", requirements={"id"="\d+"})
     */
    public function copyrightAction($id)
    {
        $req = $this->getRequest();
        $on = $req->get('on');
        $article = $this->getArticleById($id);
        $asCopyright = $on == 1;
        
        // add/remove article from magazine's copyright articles
        $issue = $article->getIssue();
        if (empty($issue)) {
            if ($asCopyright) {
                throw new Exception('Article not belongs to any issue');
            } 
        } else {
            $mag = $issue->getMagazine();
            if (empty($issue)) {
                if ($asCopyright) {
                    throw new Exception('Article not belongs to any magazine');
                } 
            } else {
                $article->setCopyrightMagazine($asCopyright ? $mag : null);
            }
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        $em->flush();
        
        return $this->redirect($this->generateUrl('issue_article_list', array('id' => $article->getIssue()->getId())));
    }
    
    /**
     * 
     * @Route("/{id}/del", name="article_del", requirements={"id" = "\d+"})
     */
    public function delAction($id)
    {
        $article = $this->getArticleById($id);
        $issueId = $article->getIssue()->getId();
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($article);
        $em->flush();
        
        return $this->redirect($this->generateUrl('issue_article_list', array('id' => $issueId)));
    }
    
    /**
     * Upload audio
     *
     * @Route("/uploadAudio", name="article_audioUpload", defaults={"_format" = "json"}, options={"expose" = true})
     * @Template()
     */
    public function uploadAudioAction()
    {
        $req = $this->getRequest();
        $articleId = $req->get('id');
        $file = $req->files->get('file');
        if ($articleId === null || empty($file)) {
            return new Response('no file');
        }
        $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
        $article = $repo->find($articleId);
        if (empty($article)) {
            return new Response('no article');
        }
        
        // move it
        $rootDir = $this->container->getParameter('kernel.root_dir');
        $oldAudio = $article->getAudio();
        if (!empty($oldAudio)) {
            @unlink("$rootDir/../web/uploads/$oldAudio");
        }
        
        $originalName = $file->getClientOriginalName();
        $audioName = uniqid('audio_') . '.' . $file->guessExtension();
        $file->move($rootDir . '/../web/uploads/', $audioName);
        $article->setAudio($audioName);
        $article->setAudioName($originalName);
        $em = $this->getDoctrine()->getEntityManager();
        $em->flush();
        
        $tplVars = array(
            'audio' => $req->getBasePath() . '/uploads/' . $audioName,
            'name' => $originalName
        );
        return new Response(json_encode($tplVars));
    }
    
    /**
     * 
     * 
     * @deprecated
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
                throw new Exception('Page need be persisted first');
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
     * @Route("/{id}/edit", name="article_edit", requirements={"id"="\d+"})
     * @Template("MagendArticleBundle:Article:new.html.twig")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
        $article = $repo->find($id);
        if (empty($article)) {
            throw new Exception('Article not found');
        }
        
        $kwRepo = $this->getDoctrine()->getRepository('MagendKeywordBundle:Keyword');
        $kws = $kwRepo->findAll();
        
        $form = $this->createForm(new ArticleType(), $article);
        $issue = $article->getIssue();
        $req  = $this->getRequest();
        if ($req->getMethod() == 'POST') {
            $form->bindRequest($req);
            if ($form->isValid()) {
                $issueId = $req->get('issueId');
                if ($issueId != $issue->getId()) {
                    $issueRepo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
                    $newIssue = $issueRepo->find($issueId);
                    if (empty($newIssue)) {
                        throw new Exception('Issue not found');
                    }
                    
                    $newIssue->addArticle($article);
                    $issue->removeArticle($article);
                    $article->setIssue($newIssue);
                }
                
                $kwText = trim($article->getKeywordsText());
                if (!empty($kwText)) {
                    $keywords = $kwRepo->toEntities(explode(',', $kwText));
                    $article->setKeywords($keywords);
                }
                
                $em->flush();
                
                $articleId = $article->getId();
                if ($req->isXmlHTTPRequest()) {
                    $response = new Response($articleId);
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;
                } else {
                    return $this->redirect($this->generateUrl('article_show', array('id' => $articleId)));
                }
            }
        }
        
        $articleIds = $issue->getArticleIds();
        $index = array_search($id, $articleIds);
        $prev = $next = null; // id of previous and next page
        if ($index !== false) {
            if (isset($articleIds[$index - 1])) {
                $prev = $articleIds[$index - 1];
            }
            if (isset($articleIds[$index + 1])) {
                $next = $articleIds[$index + 1];
            }
        }
        
        return array(
            //'institutes' => $institutes,
            'keywords'  => $kws,
            'issue'     => $issue,
            'article'   => $article,
            'form'      => $form->createView(),
            'prev'      => $prev,
            'next'      => $next,
        );
    }
    
    /**
     * $id is magazine id
     * 
     * @Route("/magazine/{id}/new", name="article_new", requirements={"id"="\d+"})
     * @Template()
     */
    public function newAction($id)
    {
        $article = new Article();
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $kwRepo = $this->getDoctrine()->getRepository('MagendKeywordBundle:Keyword');
        $kws = $kwRepo->findAll();
        
        $req  = $this->getRequest();
        $form = $this->createForm(new ArticleType(), $article);
        $issueRepo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
        
        if ($req->getMethod() == 'POST') {
            $form->bindRequest($req);
            if ($form->isValid()) {
                $issueId = $req->get('issueId');
                $issue = $issueRepo->find($issueId);
                if (empty($issue)) {
                    throw new Exception('Issue not found');
                }
                
                $kwText = trim($article->getKeywordsText());
                if (!empty($kwText)) {
                    $keywords = $kwRepo->toEntities(explode(',', $kwText));
                    $article->setKeywords($keywords);
                }
                
                $em->persist($article);
                $em->flush();
                
                $op = new Operation();
                $user = $this->get('security.context')->getToken()->getUser();
                $op->setUser($user);
                $url = $this->get('router')->generate('article_edit', array('id' => $article->getId()));
                $op->setContent('添加了文章<a href="' . $url . '">' . $article->getTitle() .  '</a>');
                $em->persist($op);
                
                $issue->noOp = true;
                $issue->addArticle($article);
                $em->flush();
                
                if ($req->isXmlHTTPRequest()) {
                    // Only return article id
                    $response = new Response($article->getId());
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;
                } else {
                    return $this->redirect($this->generateUrl('article_show', array('id' => $article->getId())));
                }
            }
        }
        
        $repo = $this->getDoctrine()->getRepository('MagendMagazineBundle:Magazine');
        $magazine = $repo->find($id);
        
        return array(
            'magazine' => $magazine,
            'keywords' => $kws,
            'article'  => $article,
            'form'     => $form->createView()
        );
    }
    
    /**
     * @Route("/{id}", name="article_show", requirements={"id" = "\d+"})
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        if ($this->getRequest()->isXmlHTTPRequest()) {
            $query = $em->createQuery('SELECT partial a.{id, title}, partial p.{id, landscapeImg, portraitImg, label} FROM MagendArticleBundle:Article a LEFT JOIN a.pages p WHERE a.id = :articleId')
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
}
