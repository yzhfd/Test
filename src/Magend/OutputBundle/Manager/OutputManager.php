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
        $article->total_pages=  array_merge($article->getMainPages(),$article->getInfoPages(),$article->getStructurePages());
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
            $repo = $this->container->get('doctrine')->getRepository('MagendMagazineBundle:Magazine');
            $magazine = $repo->find($id);
            
            $em = $this->container->get('doctrine.orm.entity_manager');
            $query = $em->createQuery("SELECT s FROM MagendIssueBundle:Issue s WHERE s.magazine = :magId ORDER BY s.createdAt DESC")
                        ->setParameter('magId', $id);
            $issues = $query->getResult();
            if (empty($issues)) {
                $issues = array();
            }
        } else {
            $issues = array();
            $magazine = array();
        }
        
        $response = $this->render('MagendOutputBundle:Output:magazine.xml.twig', array(
            'issues' => $issues,
            'magazine' => $magazine
        ));
        return $response;
    }
    
    /**
     * Output magazine xml to Publish directory
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
     * Output magazines xml to Publish directory
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
     * $user owns these magazines
     * 
     * @param User $user
     * @param bool $bResponse
     * @return Response
     */
    public function outputMagazines($user = null, $bResponse = true)
    {
        // from api?
        if (!($user instanceof User)) {
            $user = null;
        }
        
        $em = $this->container->get('doctrine.orm.entity_manager');
        $where = $user == null ? '' : 'WHERE m.owner = :user';
        $params = $user == null ? array() : array('user' => $user->getId());
        $query = $em->createQuery("SELECT m FROM MagendMagazineBundle:Magazine m $where ORDER BY m.createdAt DESC")
                    ->setParameters($params);
        $magazines = $query->getResult();
        if (empty($magazines)) {
            $magazines = array();
        }
        
        $tplVars = array('magazines' => $magazines);
        if ($bResponse) {
            $response = $this->render('MagendOutputBundle:Output:magazines.xml.twig', $tplVars);
            return $response;
        } else {
            return $tplVars;
        }
    }
}