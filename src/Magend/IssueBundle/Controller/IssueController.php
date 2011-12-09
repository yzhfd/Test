<?php

namespace Magend\IssueBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Magend\ArticleBundle\Entity\Article;
use Magend\PageBundle\Entity\Page;
use Magend\IssueBundle\Form\IssueType;
use Magend\IssueBundle\Entity\Issue;

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
        if (!$issue) {
            throw new \ Exception('issue ' . $id . ' not found');
        }
        
        return $issue;
    }
    
    /**
     * 
     * @Route("/new", name="issue_new")
     * @Template()
     */
    public function newAction()
    {
        $issue = new Issue();
        $req   = $this->getRequest();
        $form  = $this->createForm(new IssueType(), $issue);
        
        if ($req->getMethod() == 'POST') {
            $form->bindRequest($req);
            if ($form->isValid()) {                
                $em = $this->getDoctrine()->getEntityManager();
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
                    return $this->redirect($this->generateUrl('issue_show', array('id' => $issue->getId())));
                }
            }
        }
        
        return array(
            'issue' => $issue,
            'form'  => $form->createView()
        );
    }
    
    /**
     * 
     * @Route("/{id}/edit", name="issue_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $issue = $this->_findIssue($id);
        $req   = $this->getRequest();
        $form  = $this->createForm(new IssueType(), $issue);
        
        if ($req->getMethod() == 'POST') {
            $form->bindRequest($req);
            if ($form->isValid()) {                
                $em = $this->getDoctrine()->getEntityManager();
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
                    return $this->redirect($this->generateUrl('issue_show', array('id' => $issue->getId())));
                }
            }
        }
        
        return array(
            'issue' => $issue,
            'form'  => $form->createView()
        );
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
     * @Route("/update_articleIds", name="issue_update_articleIds", defaults={"_format"="json"})
     * @Method("post")
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
     * For test, delete all articles and pages belong to this issue
     * 
     * @Route("/{id}/flush", defaults={"_format"="json"})
     */
    public function flushAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $query = $em->createQuery('SELECT partial a.{id} FROM MagendArticleBundle:Article a WHERE :issueId MEMBER OF a.issues')
                    ->setParameter('issueId', $id);
        $arts = $query->getResult();
        foreach ($arts as $art) {
            $em->remove($art);
        }
        $em->flush();
        
        return new Response(json_encode(array(
            'result' => 'success'
        )));
    }
    
    /**
     * @Route("/{id}/editor", name="issue_editor")
     * @Template()
     */
    public function editorAction($id)
    {
        $issue = $this->_findIssue($id);
        
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
