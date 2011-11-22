<?php

namespace Magend\PageBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Magend\PageBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * 
 * @Route("/page")
 * @author kail
 */
class PageController extends Controller
{
    /**
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
    
    /**
     * 
     * @Route("/new", name="page_new")
     * @Template()
     */
    public function newAction()
    {
        $req = $this->getRequest();
        if ($req->isXmlHTTPRequest() && $req->getMethod() == 'POST') {
            $file = $req->files->get('file');
            // move it
            $rootDir = $this->container->getParameter('kernel.root_dir');
            $file->move($rootDir . '/../web/uploads/', uniqid('page_') . '.jpg');
            return new Response($file->getClientOriginalName());
        }
        
        return array(
            'form' => $form->createView()
        );
    }
}
