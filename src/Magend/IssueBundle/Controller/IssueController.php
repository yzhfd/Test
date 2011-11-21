<?php

namespace Magend\IssueBundle\Controller;

use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Magend\ArticleBundle\Entity\Article;
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
     * @Route("/", name="issue_show")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
        $article = $repo->find(1);
        
        if ($article == null) {
            return array('name' => 'v');
        }
        
        $name = $article->getTitle();
        $issue = $article->getIssue();
        $issue->setArticleIds(array(1,4,2));
        //$issue->getArticleIds();
        //$issues = $article->getIssues();
        //echo $issue->getTitle();exit;
        //$articles = $issue->getArticles();
        //$issues->removeElement($article->getIssue());
        $issue->setTitle('fuck vanesa');
        $em->persist($issue);
        $em->flush();
        
        return array('name' => $name);
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
         * $query = $em->createQuery('SELECT x.id FROM MagendArticleBundle:Article x WHERE :issueId MEMBER  x.issues');
         */
        
        $em = $this->getDoctrine()->getEntityManager();
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
        
        return array();
    }
}
