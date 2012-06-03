<?php

namespace Magend\OutputBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Magend\UserBundle\Entity\User;

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
     * @param integer $id
     * @return Response
     */
    public function outputArticle($id)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $query = $em->createQuery('SELECT a, k, p, h FROM MagendArticleBundle:Article a LEFT JOIN a.keywords k LEFT JOIN a.pages p LEFT JOIN p.hots h WHERE a = :article')
                    ->setParameter('article', $id);
        $article = $query->getSingleResult();
        
        $response = $this->render('MagendOutputBundle:Output:article.xml.twig', array(
            'article' => $article
        ));
        return $response;
    }
    
    /**
     * 
     * 
     * @param integer $id
     * @return Response
     */
    public function outputIssue($id)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $query = $em->createQuery('SELECT s, a FROM MagendIssueBundle:Issue s LEFT JOIN s.articles a WHERE s = :issue')
                    ->setParameter('issue', $id);
        $issue = $query->getSingleResult();
        $response = $this->render('MagendOutputBundle:Output:issue.xml.twig', array(
            'issue' => $issue
        ));
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
        if ($id !== null) {
            $repo = $this->container->get('doctrine')->getRepository('MagendMagzineBundle:Magzine');
            $magzine = $repo->find($id);
            
            $em = $this->container->get('doctrine.orm.entity_manager');
            $query = $em->createQuery("SELECT s FROM MagendIssueBundle:Issue s WHERE s.magzine = :magId ORDER BY s.createdAt DESC")
                        ->setParameter('magId', $id);
            $issues = $query->getResult();
            if (empty($issues)) {
                $issues = array();
            }
        } else {
            $issues = array();
            $magzine = array();
        }
        
        $response = $this->render('MagendOutputBundle:Output:magzine.xml.twig', array(
            'issues' => $issues,
            'magzine' => $magzine
        ));
        return $response;
    }
    
    /**
     * Output magzine xml to Publish directory
     * 
     * @param integer $id
     */
    public function outputMagazineXML($id)
    {
        $response = $this->outputMagazine($id);
        $rootDir = $this->container->getParameter('kernel.root_dir');
        $publishDir = $rootDir . '/../web/Publish/';
        file_put_contents($publishDir . "group$id.xml", $response->getContent());
    }
    
    /**
     * Output magzines xml to Publish directory
     * 
     * @param integer $id
     */
    public function outputMagazinesXML()
    {
        $response = $this->outputMagazines();
        $rootDir = $this->container->getParameter('kernel.root_dir');
        $publishDir = $rootDir . '/../web/Publish/';
        file_put_contents($publishDir . "grouplist.xml", $response->getContent());
    }
    
    /**
     * 
     * @param User $user
     * @return Response
     */
    public function outputMagazines($user = null)
    {
        // from api?
        if (!($user instanceof User)) {
            $user = null;
        }
        
        $em = $this->container->get('doctrine.orm.entity_manager');
        $where = $user == null ? '' : 'WHERE m.user = :user';
        $params = $user == null ? array() : array('user' => $user->getId());
        $query = $em->createQuery("SELECT m FROM MagendMagzineBundle:Magzine m $where ORDER BY m.createdAt DESC")
                    ->setParameters($params);
        $magzines = $query->getResult();
        if (empty($magzines)) {
            $magzines = array();
        }
        
        $tplVars = array('magzines' => $magzines);
        $response = $this->render('MagendOutputBundle:Output:magzines.xml.twig', $tplVars);
        return $response;
    }
}