<?php

namespace Magend\AssetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Asset controller
 * 
 * @Route("/asset")
 * @author Kail
 */
class AssetController extends Controller
{
    /**
     * @Route("/hello/{name}")
     * @Template()
     */
    public function indexAction($name)
    {
        return array('name' => $name);
    }
    
    /**
     * Upload asset
     * 
     * @Route("/upload/hot/{id}", name="asset_upload", defaults={"_format" = "json"}, requirements={"id"="\d+"}, options={"expose" = true})
     */
    public function uploadAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendHotBundle:Hot');
        $hot = $repo->find($id);
        if (empty($hot)) {
            return new Response(json_encode(array(
                'error' => 'hot.not_found'
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
        $fileName = uniqid('asset_') . ".$ext";
        $file->move($rootDir . '/../web/uploads/', $fileName);
        
        // @todo @unlink existing files
        $hotType = $hot->getType();
        if ($hotType == 0) { // gallery
            $assets = $hot->getAssets();
            if (!is_array($assets)) {
                $assets = array();
            }
            $assets[] = array('name' => $file->getClientOriginalName(), 'file' => $fileName);
        } else {
            //if ($hotType == 1 || $hotType == 3 || $hotType == 4) { // video audio or single image
            $assets = $hot->getAssets();
            if (!empty($assets)) {
                @unlink($rootDir . '/../web/uploads/' . $assets[0]['file']);
            }
            $assets = array(
                array('name' => $file->getClientOriginalName(), 'file' => $fileName)
            );
            //} 
        }

        $hot->setAssets($assets);
        $em = $this->getDoctrine()->getEntityManager();
        $em->flush();
        
        return new Response(json_encode($assets));
    }
}
