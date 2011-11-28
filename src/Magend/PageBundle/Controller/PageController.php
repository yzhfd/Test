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
     * Upload image
     * @todo landscape or portrait
     * 
     * @Route("/upload", name="page_upload")
     * @Template()
     */
    public function uploadAction()
    {
        $req = $this->getRequest();
        if ($req->isXmlHTTPRequest() && ($req->getMethod() == 'POST' || $req->getMethod() == 'PUT')) {
            $file = $req->files->get('file');
            // move it
            $rootDir = $this->container->getParameter('kernel.root_dir');
            $imgName = uniqid('page_') . '.' . $file->guessExtension();
            $file->move($rootDir . '/../web/uploads/', $imgName);
            $this->get('imagine.templating.helper')->filter("uploads/$imgName", 'landscapeThumb');
            return new Response($imgName);
        }
        
        return array(
            'form' => $form->createView()
        );
    }
}
