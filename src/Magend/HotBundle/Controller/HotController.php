<?php

namespace Magend\HotBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * 
 * @Route("/hot")
 * @author kail
 */
class HotController extends Controller
{
    /**
     * Upload hot's video or image
     * 
     * @Route("/{id}/upload", name="hot_upload", defaults={"_format" = "json"}, requirements={"id"="\d+"}, options={"expose" = true});
     */
    public function uploadAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendHotBundle:Hot');
        $hot = $repo->find($id);
        if (empty($hot)) {
            return new Response(json_encode(array(
                'error' => 'hot not found'
            )));
        }
        
        $req = $this->getRequest();
        $file = $req->files->get('file');
        $ext = $file->guessExtension();
        if (empty($ext)) {
            $nameArr = explode('.', $file->getClientOriginalName());
            $ext = array_pop($nameArr);
        }
        
        $rootDir = $this->container->getParameter('kernel.root_dir');
        $fileName = uniqid('hot_') . ".$ext";
        $file->move($rootDir . '/../web/uploads/', $fileName);
        
        // @todo @unlink existing files
        $hotType = $hot->getType();
        if ($hotType == 1) { // video
            $assets = $hot->getAssets();
            if (!empty($assets)) {
                @unlink($rootDir . '/../web/uploads/' . $assets[0]['file']);
            }
            $assets = array(
                array('name' => $file->getClientOriginalName(), 'file' => $fileName)
            );
        } else if ($hotType == 0) { // gallery
            $assets = $hot->getAssets();
            if (!is_array($assets)) {
                $assets = array();
            }
            $assets[] = array('name' => $file->getClientOriginalName(), 'file' => $fileName);
        }

        $hot->setAssets($assets);
        $em = $this->getDoctrine()->getEntityManager();
        $em->flush();
        
        return new Response(json_encode($assets));
    }
    
    /**
     * Order hot's assets
     * 
     * @Route("/{id}/order_assets", name="hot_order_assets", defaults={"_format" = "json"}, requirements={"id"="\d+"}, options={"expose" = true});
     */
    public function orderAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendHotBundle:Hot');
        $hot = $repo->find($id);
        if (empty($hot)) {
            return new Response(json_encode(array(
                'error' => 'hot not found'
            )));
        }
    }
}
