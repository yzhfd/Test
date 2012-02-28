<?php

namespace Magend\AssetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Magend\AssetBundle\Entity\Asset;

/**
 * Asset controller
 * 
 * @Route("/asset")
 * @author Kail
 */
class AssetController extends Controller
{
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
        
        /*
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
        }*/
        
        $em = $this->getDoctrine()->getEntityManager();
        
        $asset = new Asset();
        $asset->setTag($file->getClientOriginalName());
        $asset->setResource($fileName);
        $em->persist($asset);
        $em->flush();
        
        $hot->addAsset($asset);
        $em->flush();
        
        return new Response(json_encode($hot->getAssets()));
    }
}
