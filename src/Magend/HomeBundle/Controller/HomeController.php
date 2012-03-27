<?php

namespace Magend\HomeBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * 
 * @author Kail
 */
class HomeController extends Controller
{
    /**
     * @Route("/dashboard", name="home")
     * @Template()
     */
    public function indexAction()
    {
        $req = $this->getRequest();
        $magId = $req->cookies->get('magzine_id');
        $magzine = null;
        if ($magId !== null) {
            $repo = $this->getDoctrine()->getRepository('MagendMagzineBundle:Magzine');
            $magzine = $repo->find($magId);
        }
        
        return array(
            'magzine' => $magzine
        );
    }
    
    private function _getDirectoryList ($directory) 
    {
        // create an array to hold directory list
        $results = array();
        
        // create a handler for the directory
        $handler = opendir($directory);
        
        // open directory and walk through the filenames
        while ($file = readdir($handler)) {
            // if file isn't this directory or its parent, add it to the results
            if ($file != '.' && $file != '..' && substr($file, 0, 1) != '.') {
                $fileArr = array();
                $fileArr['name'] = $file;
                $fileArr['timestamp'] = filemtime($directory . $file);
                $results[] = $fileArr;
            }
        }

        // tidy up: close the handler
        closedir($handler);
        
        // done!
        return $results;
    }
    
    /**
     * List files under Publish directory
     * 
     * @Route("/published", name="published")
     * @Template()
     */
    public function publishedAction()
    {
        $rootDir = $this->container->getParameter('kernel.root_dir');
        $publishDir = $rootDir . '/../web/Publish/';
        $files = $this->_getDirectoryList($publishDir);
        
        return array('files' => $files);
    }
}
