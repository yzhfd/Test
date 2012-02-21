<?php

namespace Magend\IssueBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Magend\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Magend\ArticleBundle\Entity\Article;
use Magend\PageBundle\Entity\Page;
use Magend\IssueBundle\Form\IssueType;
use Magend\IssueBundle\Entity\Issue;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\ Exception\OutOfRangeCurrentPageException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * 
 * @Route("/issue")
 * @author kail
 */
class IssueController extends Controller
{
    private function _findIssue($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
        $issue = $repo->find($id);
        
        return $issue;
    }
    
    /**
     * 
     * @Route("/{id}/insert-copyright", name="insert_copyright")
     */
    public function insertCopyrightAction($id)
    {
        $issue = $this->_findIssue($id);
        if (empty($issue)) {
            throw new \ Exception('Issue not found'); 
        }
        
        $cpr = $issue->getMagzine()->getCopyrightArticle();
        $articleIds = $issue->getArticleIds();
        if (in_array($cpr->getId(), $articleIds)) {
            throw new \ Exception('Already inserted'); 
        }
        $issue->addArticle($cpr);
        if (count($articleIds) >= 1) {
            array_splice($articleIds, 1, 0, $cpr->getId()); 
        }
        $issue->setArticleIds($articleIds);
        $em = $this->getDoctrine()->getEntityManager();
        $em->flush();
        
        return $this->redirect($this->generateUrl('issue_article_list', array('id' => $issue->getId())));
    }
    
    /**
     * Remove copyright article
     *
     * @Route("/{id}/remove-copyright", name="remove_copyright", requirements={"id"="\d+"})
     */
    public function removeCopyrightAction($id)
    {
        $issue = $this->_findIssue($id);
        if (empty($issue)) {
            throw new \ Exception('Issue not found'); 
        }
        
        $cpArticle = $issue->getMagzine()->getCopyrightArticle();
        $issue->removeArticle($cpArticle);
        $em = $this->getDoctrine()->getEntityManager();
        $em->flush();
        
        return $this->redirect($this->generateUrl('issue_article_list', array('id' => $id)));
    }
    
    /**
     * 
     * @Route("/{id}/publish", name="issue_publish", defaults={"_format" = "json"})
     */
    public function publishAction($id)
    {
        $issue = $this->_findIssue($id);
        if (empty($issue)) {
            return new Response('{"msg":"期刊不存在"}'); 
        }
        if ($issue->getPublish()) {
            return new Response('{"msg":"期刊已发布"}'); 
        }
        
        $issue->setPublish(true);
        if ($issue->getPublishedAt() === null) {
            $issue->setPublishedAt(new \DateTime());
        }
        $em = $this->getDoctrine()->getEntityManager();
        $em->flush();
        
        $pubAt = $issue->getPublishedAt()->format('Y-m-d');
        return new Response('{"msg":"发布成功", "publishedAt":"' . $pubAt . '" }');
    }
    
    /**
     * Abstract new and edit actions
     * 
     * @param Issue $issue
     */
    private function _formRet($issue)
    {
        $form = $this->createForm(new IssueType(), $issue);
        if ($this->getRequest()->getMethod() == 'POST') {
            $ret = $this->_submit($form, $issue);
            if ($ret) {
                return $ret;
            }
        }
        
        $magRepo = $this->getDoctrine()->getRepository('MagendMagzineBundle:Magzine');
        $mags = $magRepo->findAll();
        return array(
            'issue'    => $issue,
            'magzines' => $mags,
            'form'     => $form->createView()
        );        
    }
    
    /**
     * Delete cover or preview
     * @Route("/delImg", name="issue_imgDel", defaults={"_format" = "json"}, options={"expose" = true})
     */
    public function delImgAction()
    {
        $req = $this->getRequest();
        $issueId = $req->get('id');
        $issue = $this->_findIssue($issueId);
        if (empty($issue)) {
            return new Response('no issue');
        }
        
        $img = $req->get('img');
        $getter = "get$img";
        if (method_exists($issue, $getter)) {
            $imgName = $issue->$getter();
            $setter = "set$img";
            $issue->$setter(null);
            $rootDir = $this->container->getParameter('kernel.root_dir');
            @unlink($rootDir . '/../web/uploads/'. $imgName);
            $em = $this->getDoctrine()->getEntityManager();
            $em->flush();        
        }
        
        return new Response('done');
    }
    
    /**
     * Upload cover (landscape or portrait, not both by dnd), and preview images
     * 
     * @todo refactor all dnd uploads
     * @Route("/uploadImg", name="issue_imgUpload", defaults={"_format" = "json"}, options={"expose" = true})
     */
    public function uploadImgAction()
    {
        $req = $this->getRequest();
        $issueId = $req->get('id');
        $landscapeCover = $req->files->get('landscapeCover');
        $portraitCover = $req->files->get('portraitCover');
        $preview = $req->files->get('preview');
        if ($issueId === null || (empty($landscapeCover) && empty($portraitCover) && empty($preview))) {
            return new Response('no file');
        }
        
        $issue = $this->_findIssue($issueId);
        if (empty($issue)) {
            return new Response('no issue');
        }
        
        // move it
        $rootDir = $this->container->getParameter('kernel.root_dir');
        $oldImg = null;
        $imgFile = null;
        $setterName = null;
        if ($landscapeCover) {
            $oldImg = $issue->getLandscapeCover();
            $imgFile = $landscapeCover;
            $setterName = 'setLandscapeCover';
        } else if ($portraitCover) {
            $oldImg = $issue->getPortraitCover();
            $imgFile = $portraitCover;
            $setterName = 'setPortraitCover';
        } else if ($preview) {
            $oldImg = $issue->getPreview();
            $imgFile = $preview;
            $setterName = 'setPreview';
        }
        if (!empty($oldImg)) {
            @unlink("$rootDir/../web/uploads/$oldImg");
        }
        
        $imgName = uniqid('issue_') . '.' . $imgFile->guessExtension();
        $imgFile->move($rootDir . '/../web/uploads/', $imgName);
        $issue->$setterName($imgName);
        $em = $this->getDoctrine()->getEntityManager();
        $em->flush();
        
        $tplVars = array(
            'img' => $req->getBasePath() . '/uploads/' . $imgName
        );
        return new Response(json_encode($tplVars));
    }
    
    /**
     * Upload audio
     * 
     * @todo refactor with article audio upload
     * @Route("/uploadAudio", name="issue_audioUpload", defaults={"_format" = "json"}, options={"expose" = true})
     */
    public function uploadAudioAction()
    {
        $req = $this->getRequest();
        $issueId = $req->get('id');
        $file = $req->files->get('file');
        if ($issueId === null || empty($file)) {
            return new Response('no file');
        }
        
        $issue = $this->_findIssue($issueId);
        if (empty($issue)) {
            return new Response('no issue');
        }
        
        // move it
        $rootDir = $this->container->getParameter('kernel.root_dir');
        $oldAudio = $issue->getAudio();
        if (!empty($oldAudio)) {
            @unlink("$rootDir/../web/uploads/$oldAudio");
        }
        
        $originalName = $file->getClientOriginalName();
        $audioName = uniqid('audio_') . '.' . $file->guessExtension();
        $file->move($rootDir . '/../web/uploads/', $audioName);
        $issue->setAudio($audioName);
        $issue->setAudioName($originalName);
        $em = $this->getDoctrine()->getEntityManager();
        $em->flush();
        
        $tplVars = array(
            'audio' => $req->getBasePath() . '/uploads/' . $audioName,
            'name' => $originalName
        );
        return new Response(json_encode($tplVars));
    }
    
    /**
     * Get default issue nos
     * 
     * @Route("/nos", name="issuenos", defaults={"_format" = "json"}, options={"expose" = true})
     */
    public function issueNoAction()
    {
        $req = $this->getRequest();
        $magId = $req->get('magzineId');
        $em = $this->getDoctrine()->getEntityManager();
        $query = $em->createQuery('SELECT s FROM MagendIssueBundle:Issue s WHERE s.magzine = :magId ORDER BY s.totalIssueNo DESC')
                    ->setParameter('magId', $magId)
                    ->setMaxResults(1);
        try {
            $issue = $query->getSingleResult();
        } catch (\ Exception $e) {
            return new Response('');
        }
        
        $tplVars = array(
            'yearIssueNo' => $issue->getYearIssueNo(),
            'totalIssueNo' => $issue->getTotalIssueNo()
        );
        
        return new Response(json_encode($tplVars));
    }
    
    /**
     * 
     * @Route("/new", name="issue_new")
     * @Template()
     */
    public function newAction()
    {
        $req = $this->getRequest();
        $issue = new Issue();
        $em = $this->getDoctrine()->getEntityManager();
        $magId = $req->get('magzineId', $req->cookies->get('magzine_id'));
        if ($req->getMethod() == 'GET' && $magId !== null) {
            $magzine = $em->getReference('MagendMagzineBundle:Magzine', $magId);
            $query = $em->createQuery('SELECT s FROM MagendIssueBundle:Issue s WHERE s.magzine = :magId ORDER BY s.totalIssueNo DESC')
                        ->setParameter('magId', $magId)
                        ->setMaxResults(1);
            try {
                $latestIssue = $query->getSingleResult();
                
                $totalIssueNo = $latestIssue->getTotalIssueNo();
                $yearIssueNo = $latestIssue->getYearIssueNo();
                //$yearIssueNo
                $issue->setMagzine($magzine);
                $issue->setYearIssueNo($yearIssueNo);
                $issue->setTotalIssueNo($totalIssueNo + 1);
            } catch (\ Exception $e) {
                // do nothing
            }
        }
        
        return $this->_formRet($issue);
    }
    
    /**
     * 
     * @Route("/{id}/edit", name="issue_edit")
     * @Template("MagendIssueBundle:Issue:new.html.twig")
     */
    public function editAction($id)
    {
        $issue = $this->_findIssue($id);
        
        $isPublished = $issue->getPublish();
        if ($isPublished) {
            return $this->container->get('templating')->renderResponse(
                'MagendIssueBundle:Issue:noedit.html.twig'
            );
        }
        
        return $this->_formRet($issue);
    }
    
    /**
     * For new and edit
     * 
     */
    private function _submit($form, $issue)
    {
        $req = $this->getRequest();
        $form->bindRequest($req);
        if ($form->isValid()) {                
            $em = $this->getDoctrine()->getEntityManager();
            $magzineId = $req->get('magzineId');
            $magRepo = $this->getDoctrine()->getRepository('MagendMagzineBundle:Magzine');
            $mag = $magRepo->find($magzineId);
            if (empty($mag)) {
                throw new \ Exception('magzine ' . $magzineId . ' not found');
            }
            $issue->setMagzine($mag);
            $em->persist($issue);
            $em->flush();
            
            if ($req->isXmlHTTPRequest()) {
                $id = $issue->getId();
                $response = new Response(json_encode(array(
                    'id'        => $id,
                    'editorUrl' => $this->generateUrl('issue_editor', array('id' => $id))
                )));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            } else {
                return $this->redirect($this->generateUrl('issue_edit', array('id' => $issue->getId()))); //issue_article_list
                // return $this->redirect($this->generateUrl('issue_show', array('id' => $issue->getId())));
            }
        }
        //var_dump( $form->getErrors() );exit;
        return null;
    }
    
    /**
     * Only return articleIds if by ajax
     * 
     * @Route("/{id}", name="issue_show", requirements={"id"="\d+"})
     * @Template()
     */
    public function showAction($id)
    {        
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
        $issue = $repo->find($id);
        if (!$issue) {
            throw new \ Exception('issue ' . $id . ' not found');
        }
        
        $req = $this->getRequest();
        if ($req->isXmlHTTPRequest()) {
            return new Response(json_encode(array(
                'articleIds' => $issue->getArticleIds()
            )));
        }
        
        return array(
            'issue' => $issue
        );
    }
    
    /**
     * 
     * @Route("/list", name="issue_list")
     */
    public function listAction()
    {
        $req = $this->getRequest();
        $magId = $req->cookies->get('magzine_id');
        if ($magId === null) {
            $em = $this->getDoctrine()->getEntityManager();
            $query = $em->createQuery('SELECT m.id FROM MagendMagzineBundle:Magzine m ORDER BY m.createdAt DESC');
            $query->setMaxResults(1);
            $magId = $query->getSingleScalarResult();
        }
        return new RedirectResponse($this->generateUrl('magzine_issues', array(
            'id' => $magId
        )));
    }
    
    /**
     * @Route("/update_articleIds", name="issue_update_articleIds", defaults={"_format"="json"})
     * @method("post")
     */
    public function updateArticleIdsAction()
    {
        $req = $this->getRequest();
        $id = $req->get('id');
        
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
        $issue = $repo->find($id);
        if (!$issue) {
            throw new \ Exception('issue ' . $id . ' not found');
        }
        
        $issue->setArticleIds($req->get('articleIds'));
        $em->flush();
        return new Response();
    }
    
    /**
     * Get issue's articles with article's pages, 
     * ordered according to ids text
     * 
     * @Route("/{id}/articles", name="issue_articles", defaults={"_format"="json"})
     */
    public function articlesAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $dql = 'SELECT partial a.{id, title, pageIds}, partial p.{id, landscapeImg, portraitImg, label} FROM MagendArticleBundle:Article a INDEX BY a.id LEFT JOIN a.pages p WHERE :issueId MEMBER OF a.issues';
        $query = $em->createQuery($dql)->setParameter('issueId', $id);
        
        $arr = $query->getArrayResult();
        $result = array();
        // order articles according to article_ids, order 
        foreach ($arr as $articleId=>$articleArr) {
            $pages = array_values($articleArr['pages']);
            $articleArr['pages'] = $pages;
            $result[] = $articleArr;
        }
        
        /*
        $repo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
        $issue = $repo->find($id);
        if ($issue == null) {
            return new Response('');
        }
        */
        return new Response(json_encode($result));
    }
    
    /**
     * 
     * @Route("/{id}/article/list", name="issue_article_list", requirements={"id"="\d+"})
     * @Template()
     */
    public function articleListAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
        $issue = $repo->find($id);
        if (!$issue) {
            throw new \ Exception('issue ' . $id . ' not found');
        }
        
        // select pages and associate with articles
        $em = $this->getDoctrine()->getEntityManager();
        $dql = 'SELECT a, p FROM MagendArticleBundle:Article a LEFT JOIN a.pages p WHERE a in (:articles)';
        $q = $em->createQuery($dql)->setParameter('articles', $issue->getArticleIds());
        $q->getResult();
        
        // select keywords and associate with articles
        // shit, I cannot get this done - if article has no keywords, then article->getKeywords() will
        // still query database
        /*
        $dql = 'SELECT a, k FROM MagendArticleBundle:Article a LEFT JOIN a.keywords k WHERE a in (:articles)';
        $q = $em->createQuery($dql)->setParameter('articles', $issue->getArticleIds());
        $articles = $q->getResult();*/
        
        return array('issue' => $issue);
    }
    
    /**
     * 
     * @Route("/{id}/layout", name="issue_layout", requirements={"id"="\d+"}, defaults={"_format"="json"})
     * 
     */
    public function layoutAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
        $issue = $repo->find($id);
        if (!$issue) {
            throw new \ Exception('issue ' . $id . ' not found');
        }

        $req = $this->getRequest();
        $articles = $req->get('articles');
        if (empty($articles)) {
            return new Response('{ "error":1 }');
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        $query = $em->createQuery('SELECT partial a.{id, pageIds} FROM MagendArticleBundle:Article a INDEX BY a.id WHERE :issueId MEMBER OF a.issues')
                    ->setParameter('issueId', $id);
        $arts = $query->getResult();
        
        foreach ($articles as $articleId=>$pageIds) {
            if (isset($arts[$articleId])) {
                if ($pageIds != $arts[$articleId]->getPageIds()) {
                    $arts[$articleId]->setPageIds($pageIds);
                }
            }
        }
        
        $articleIds = $req->get('articleIds');
        if ($issue->getArticleIds() != $articleIds) {
            $issue->setArticleIds($articleIds);
        }
        $em->flush();
        return new Response('{ "success":1 }');
    }
    
    /**
     * Delete all articles and pages belong to this issue
     * May need provide method to just delete issue and associations with articles,
     * not article themselves
     * // , defaults={"_format"="json"}
     * @Route("/{id}/flush", name="issue_flush")
     */
    public function flushAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
        $issue = $repo->find($id);
        if (!$issue) {
            throw new \ Exception('No such issue');
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        $query = $em->createQuery('SELECT partial a.{id} FROM MagendArticleBundle:Article a WHERE :issueId MEMBER OF a.issues')
                    ->setParameter('issueId', $id);
        $arts = $query->getResult();
        foreach ($arts as $art) {
            $em->remove($art);
        }
        
        $em->remove($issue);        
        $em->flush();
        
        return $this->redirect($this->generateUrl('issue_list'));
    }
    
    /**
     * @Route("/{id}/editor", name="issue_editor")
     * @Template()
     */
    public function editorAction($id)
    {
        $issue = $this->_findIssue($id);
        if (!$issue) {
            throw new \ Exception('issue ' . $id . ' not found');
        }
        
        
        /*
         * find articles that belong to no issue
         * $query = $em->createQuery('SELECT x.id FROM MagendArticleBundle:Article x WHERE x.issues IS EMPTY');
         * 
         * $query = $em->createQuery('SELECT x.id FROM MagendArticleBundle:Article x WHERE :issueId MEMBER OF x.issues');
        
        $em = $this->getDoctrine()->getEntityManager();
        $query = $em->createQuery('SELECT a, p FROM MagendArticleBundle:Article a INDEX BY a.id JOIN a.pages p WHERE a.id IN (1,2,3,4,5)');
        $res = $query->getArrayResult();
        echo '<pre>';
        print_r($res);
        echo '</pre>';
         */
        
        
        /*
        $repo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
        $issue = $repo->find(1);
        
        $articles = $issue->getArticles();
        $keys = array_keys($articles);
        var_dump($keys);exit;
        
        $articleExist = true;
        $articleRef = $em->getReference('MagendArticleBundle:Article', 3);
        try {
            $issue->addArticle($articleRef);
        } catch (EntityNotFoundException $e) {
            // ignore
            // die($e->getMessage());
            $articleExist = false;
        }
        
        if ($articleExist) {
            $em->persist($issue);
            $em->flush();
        }
        */
        return array(
            'issue' => $issue
        );
    }
}
