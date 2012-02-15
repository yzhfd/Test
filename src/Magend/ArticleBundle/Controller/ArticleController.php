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
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * 
 * @Route("/article")
 * @author kail
 */
class ArticleController extends Controller
{
    private function getArticleById($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
        $article = $repo->find($id);
        if (empty($article)) {
            throw new \ Exception('Article not found');
        }
        
        return $article;
    }
    
    /**
     * Set the article as copyright
     * 
     * @Route("/{id}/copyright", name="article_copyright", requirements={"id" = "\d+"})
     */
    public function copyrightAction($id)
    {
        $req = $this->getRequest();
        $on = $req->get('on');
        $article = $this->getArticleById($id);
        $asCopyright = $on == 1;
        
        // add/remove article from magzine's copyright articles
        $issue = $article->getIssue();
        if (empty($issue)) {
            if ($asCopyright) {
                throw new \ Exception('Article not belongs to any issue');
            } 
        } else {
            $mag = $issue->getMagzine();
            if (empty($issue)) {
                if ($asCopyright) {
                    throw new \ Exception('Article not belongs to any magzine');
                } 
            } else {
                $article->setCopyrightMagzine($asCopyright ? $mag : null);
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
     * @Route(
     *     "/orderpages/{type}",
     *     name="article_orderpages",
     *     defaults={"_format" = "json"},
     *     requirements={"type"="[0-2]"},
     *     options={"expose" = true}
     * )
     */
    public function orderPagesAction($type = Page::TYPE_MAIN)
    {
        $req = $this->getRequest();
        $articleId = $req->get('id');
        $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
        $article = $repo->find($articleId);
        if (!$article || !$req->get('pageIds')) {
            return new Response(json_encode(array('result'=>0)));
        }
        
        $pageIds = $req->get('pageIds');
        $article->setPageIds($pageIds, $type);
        
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($article);
        $em->flush();
        
        return new Response('{"success":1}');
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
        
        $req  = $this->getRequest();
        $form = $this->createForm(new ArticleType(), $article);
        
        $issue = $article->getIssue();
        
        if ($req->getMethod() == 'POST') {
            $form->bindRequest($req);
            if ($form->isValid()) {
                $issueId = $req->get('issueId');
                if ($issueId != $issue->getId()) {
                    $issueRepo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
                    $newIssue = $issueRepo->find($issueId);
                    if (empty($newIssue)) {
                        throw new \ Exception('Issue not found');
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
        
        $magRepo = $this->getDoctrine()->getRepository('MagendMagzineBundle:Magzine');
        $mags = $magRepo->findAll();
        return array(
            //'institutes' => $institutes,
            'keywords'   => $kws,
            'issue'      => $issue,
            'magzines'   => $mags,
            'article'    => $article,
            'form'       => $form->createView()
        );
    }
    
    /**
     * 
     * @Route("/new", name="article_new")
     * @Template()
     */
    public function newAction()
    {        
        $article = new Article();
        
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
                    throw new \ Exception('Issue not found');
                }
                
                $kwText = trim($article->getKeywordsText());
                if (!empty($kwText)) {
                    $keywords = $kwRepo->toEntities(explode(',', $kwText));
                    $article->setKeywords($keywords);
                }
                
                $article->setIssue($issue);
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($article);
                $em->flush();
                
                $articleId = $article->getId();
                
                $issue->addArticle($article);
                $articleIds = $issue->getArticleIds();
                $articleIds[] = $articleId;
                $issue->setArticleIds($articleIds);
                $em->persist($issue);
                $em->flush();
                
                if ($req->isXmlHTTPRequest()) {
                    // Only return article id
                    $response = new Response($articleId);
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;
                } else {
                    return $this->redirect($this->generateUrl('article_show', array('id' => $articleId)));
                }
            }
        }
        
        $magRepo = $this->getDoctrine()->getRepository('MagendMagzineBundle:Magzine');
        $mags = $magRepo->findAll();
        $tplVars = array(
            //'institutes' => $institutes,
            'keywords'   => $kws,
            'magzines'   => $mags,
            'article'    => $article,
            'form'       => $form->createView()
        );
        
        $issueId = $req->get('id');
        if ($issueId !== null) {
            $issue = $issueRepo->find($issueId);
            if ($issue) {
                $tplVars['issue'] = $issue;
            }
        }

        return $tplVars;
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
