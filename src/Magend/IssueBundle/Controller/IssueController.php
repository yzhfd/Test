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
use Pagerfanta\Exception\OutOfRangeCurrentPageException;

/**
 * 
 * @Route("/issue")
 * @author kail
 */
class IssueController extends Controller
{
    private function _findIssue($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
        $issue = $repo->find($id);
        
        return $issue;
    }
    
    /**
     * Abstract new and edit actions
     * 
     * @param Issue $issue
     */
    private function _formRet($issue)
    {
        $form  = $this->createForm(new IssueType(), $issue);
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
     * 
     * @Route("/new", name="issue_new")
     * @Template()
     */
    public function newAction()
    {
        return $this->_formRet(new Issue());
    }
    
    /**
     * 
     * @Route("/{id}/edit", name="issue_edit")
     * @Template("MagendIssueBundle:Issue:new.html.twig")
     */
    public function editAction($id)
    {
        $issue = $this->_findIssue($id);
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
                return $this->redirect($this->generateUrl('issue_article_list', array('id' => $issue->getId())));
                // return $this->redirect($this->generateUrl('issue_show', array('id' => $issue->getId())));
            }
        }
        
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
        $query = $em->createQuery('SELECT partial a.{id, title, pageIds}, partial p.{id, landscapeImg, portraitImg, label} FROM MagendArticleBundle:Article a INDEX BY a.id LEFT JOIN a.pages p WHERE :issueId MEMBER OF a.issues')
                    ->setParameter('issueId', $id);
        
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
        /*
        // no pager as we need layout
        $em = $this->getDoctrine()->getEntityManager();
        $query = $em->createQuery('SELECT a FROM MagendArticleBundle:Article a WHERE :issueId MEMBER OF a.issues')
                    ->setParameter('issueId', $id);
        $tplVars = $this->getList('MagendArticleBundle:Article', $query);
        $tplVars['articles'] = $tplVars['entities'];
        unset($tplVars['entities']);
        $tplVars['issue'] = $issue;*/
        
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
