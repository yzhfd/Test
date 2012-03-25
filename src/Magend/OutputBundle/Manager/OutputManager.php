<?php

namespace Magend\OutputBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;

class OutputManager {
    
    private $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    private function render($view, array $parameters = array(), Response $response = null)
    {
        return $this->container->get('templating')->renderResponse($view, $parameters, $response);
    }
    
    /**
     * 
     * 
     * @param integer $id
     * @return Response
     */
    public function outputIssue($id)
    {
        $repo = $this->container->get('doctrine')->getRepository('MagendIssueBundle:Issue');
        $issue = $repo->find($id);
        
        // @todo refactor query
        $em = $this->container->get('doctrine.orm.entity_manager');
        $query = $em->createQuery('SELECT s, a, p, h FROM MagendIssueBundle:Issue s LEFT JOIN s.articles a LEFT JOIN a.pages p LEFT JOIN p.hots h WHERE s = :issue')
                    ->setParameter('issue', $issue);
        $query->getResult();
        
        $query = $em->createQuery('SELECT a, k FROM MagendArticleBundle:Article a LEFT JOIN a.keywords k WHERE a in (:articles)')
                    ->setParameter('articles', $issue->getArticleIds());
        $query->getResult();
        
        $tplVars = array(
            'issue' => $issue
        );
        $response = $this->render('MagendOutputBundle:Output:issue.xml.twig', $tplVars);
        return $response;
    }
    
    /**
     * 
     * 
     * @param integer $id
     * @return Response
     */
    public function outputMagazine($id)
    {
        $repo = $this->container->get('doctrine')->getRepository('MagendMagzineBundle:Magzine');
        $magzine = $repo->find($id);
        
        $em = $this->container->get('doctrine.orm.entity_manager');
        $query = $em->createQuery("SELECT s FROM MagendIssueBundle:Issue s WHERE s.magzine = :magId ORDER BY s.createdAt DESC")
                    ->setParameter('magId', $id);
        $issues = $query->getResult();
        if (empty($issues)) {
            $issues = array();
        }
        
        $response = $this->render('MagendOutputBundle:Output:magzine.xml.twig', array(
            'issues' => $issues,
            'magzine' => $magzine
        ));
        return $response;
    }
    
    /**
     * 
     * @return Response
     */
    public function outputMagazines()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $query = $em->createQuery("SELECT m FROM MagendMagzineBundle:Magzine m ORDER BY m.createdAt DESC");
        $magzines = $query->getResult();
        if (empty($magzines)) {
            $magzines = array();
        }
        
        $tplVars = array('magzines' => $magzines);
        $response = $this->render('MagendOutputBundle:Output:magzines.xml.twig', $tplVars);
        return $response;
    }
}