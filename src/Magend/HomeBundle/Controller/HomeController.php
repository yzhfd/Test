<?php

namespace Magend\HomeBundle\Controller;

use Exception;
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
        $magId = $this->getRequest()->cookies->get('magazine_id');
        $magazine = null;
        
        if ($magId !== null) {
            $repo = $this->getDoctrine()->getRepository('MagendMagazineBundle:Magazine');
            $isAdmin = $this->get('security.context')->isGranted('ROLE_ADMIN');
            if (!$isAdmin) {
                $user = $this->get('security.context')->getToken()->getUser();
                $dql = 'SELECT m FROM MagendMagazineBundle:Magazine m LEFT JOIN m.staffUsers u WHERE (m.owner = :user OR u = :user) AND m.id = :mag';
                $em = $this->getDoctrine()->getEntityManager();
                $q = $em->createQuery($dql)->setParameter('user', $user->getId())->setParameter('mag', $magId);
                try {
                    $magazine = $q->getSingleResult();
                } catch (Exception $e) {
                    $magazine = null;
                }
            } else {
                $magazine = $repo->find($magId);
            }
        }
        
        return array(
            'magazine' => $magazine
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
