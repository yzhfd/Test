<?php

namespace Magend\IssueBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityNotFoundException;
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
                
                return $this->redirect($this->generateUrl('issue_show', array('id' => $issue->getId())));
            }
        }
        
        return array(
            'issue' => $issue,
            'form'  => $form->createView()
        );
    }
    
    /**
     * @Route("/show/{id}", name="issue_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
        $issue = $repo->find($id);
        
        return array(
            'issue' => $issue
        );
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
     * @Route("/test")
     * @Template()
     */
    public function testAction()
    {
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
        return array();
    }
}
