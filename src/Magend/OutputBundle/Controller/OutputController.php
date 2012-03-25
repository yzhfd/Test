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
     * Output magzine's content(issues)
     * 
     * @Route("/magzine/{id}", name="output_magzine", requirements={"id"="\d+"}, defaults={"_format" = "xml"})
     * @Template()
     */
    public function magzineAction($id)
    {
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
        $om = $this->get('magend.output_manager');
        return $om->outputMagazines();
    }
}
