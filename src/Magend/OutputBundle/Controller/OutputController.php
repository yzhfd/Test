<?php

namespace Magend\OutputBundle\Controller;

use Magend\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Output to clients, mainly for mobiles
 * 
 * @Route("/output")
 * @author Kail
 */
class OutputController extends Controller
{
   
    
    // @todo need some authentication
    
    /**
     * Output issue content
     * 
     * @Route("/issue/{id}", name="output_issue", requirements={"id"="\d+"}, defaults={"_format" = "xml"})
     * @Template()
     */
    public function issueAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendIssueBundle:Issue');
        $issue = $repo->find($id);
        
        // @todo refactor query
        $em = $this->getDoctrine()->getEntityManager();
        $query = $em->createQuery('SELECT s, a, p, h FROM MagendIssueBundle:Issue s LEFT JOIN s.articles a LEFT JOIN a.pages p LEFT JOIN p.hots h WHERE s = :issue')
                    ->setParameter('issue', $issue);
        $query->getResult();
        
        $query = $em->createQuery('SELECT a, k FROM MagendArticleBundle:Article a LEFT JOIN a.keywords k WHERE a in (:articles)')
                    ->setParameter('articles', $issue->getArticleIds());
        $query->getResult();
        
        // test
        $response = $this->render('MagendOutputBundle:Output:issue.xml.twig', array(
            'issue' => $issue,
        ));
        $rootDir = $this->container->getParameter('kernel.root_dir');
        
        file_put_contents($rootDir . '/../web/uploads/issue.xml', $response->getContent());
        
        return array(
            'issue' => $issue
        );
    }
    
    /**
     * Output magzine's content(issues)
     * 
     * @Route("/magzine/{id}", name="output_magzine", requirements={"id"="\d+"}, defaults={"_format" = "xml"})
     * @Template()
     */
    public function magzineAction($id)
    {
        $cls = 'MagendIssueBundle:Issue';
        $em = $this->getDoctrine()->getEntityManager();
        // @todo which order
        $query = $em->createQuery("SELECT s FROM $cls s WHERE s.magzine = :magId ORDER BY s.createdAt DESC")
                    ->setParameter('magId', $id);
        $arr = $this->getList($cls, $query);
        $arr['issues'] = $arr['entities'];
        unset($arr['entities']);
        
        return $arr;
    }
    
    /**
     * 
     * @Route("/magzines", name="output_magzines", defaults={"_format" = "xml"})
     * @Template()
     */
    public function magzinesAction()
    {
        $arr = $this->getList('MagendMagzineBundle:Magzine');
        $arr['magzines'] = $arr['entities'];
        unset($arr['entities']);
        return $arr;
    }
}
