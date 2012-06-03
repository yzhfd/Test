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
        $om = $this->get('magend.output_manager');
        return $om->outputIssue($id);
    }
    
    /**
     * Output article content
     *
     * @Route("/article/{id}", name="output_article", requirements={"id"="\d+"}, defaults={"_format" = "xml"})
     * @Template()
     */
    public function articleAction($id)
    {
        $om = $this->get('magend.output_manager');
        return $om->outputArticle($id);
    }
    
    /**
     * Output magzine's content(issues)
     * 
     * @Route("/magzine/{id}", name="output_magzine", requirements={"id"="\d+"}, defaults={"_format" = "xml"})
     * @Template()
     */
    public function magzineAction($id)
    {
        /*
        $isAdmin = $this->get('security.context')->isGranted('ROLE_ADMIN');
        if (!$isAdmin) {
            $user = $this->get('security.context')->getToken()->getUser();
            $repo = $this->getDoctrine()->getRepository('MagendMagzineBundle:Magzine');
            $mag = $repo->findBy(array(
                'id' => $id,
                'user' => $user->getId()
            ));
            if (empty($mag)) {
                $id = null;
            }
        }*/
        
        $om = $this->get('magend.output_manager');
        return $om->outputMagazine($id);
    }
    
    /**
     * 
     * @Route("/magzines", name="output_magzines", defaults={"_format" = "xml"})
     * @Template()
     */
    public function magzinesAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        
        $om = $this->get('magend.output_manager');
        return $om->outputMagazines($user);
    }
}
