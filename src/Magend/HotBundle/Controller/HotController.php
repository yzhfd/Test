<?php

namespace Magend\HotBundle\Controller;

use Magend\HotBundle\Form\Type\DynamicType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Magend\HotBundle\Entity\Hot;

/**
 * 
 * @Route("/hot")
 * @author kail
 */
class HotController extends Controller
{
    /**
     * Fix hots in old data
     * 
     * @Route("/fix")
     */
    public function fixAction()
    {
        $repo = $this->getDoctrine()->getRepository('MagendHotBundle:Hot');
        $hots = $repo->findAll();
        foreach ($hots as $hot) {
            $attrs = $hot->getAttrs();
            
            if (isset($attrs['x'])) $hot->setX($attrs['x']);
            if (isset($attrs['y'])) $hot->setY($attrs['y']);
            if (isset($attrs['width'])) $hot->setW($attrs['width']);
            if (isset($attrs['height'])) $hot->setH($attrs['height']);
            $hot->setAttrs(array());
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        $em->flush();
        
        echo 'done';
        exit;
    }
    
    
    /**
     * Order hot's assets and delete ones not exist any longer
     * 
     * @Route("/{id}/order_assets", name="hot_order_assets", defaults={"_format" = "json"}, requirements={"id"="\d+"}, options={"expose" = true});
     */
    public function orderAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendHotBundle:Hot');
        $hot = $repo->find($id);
        if (empty($hot)) {
            return new Response(json_encode(array(
                'error' => 'hot.not_found'
            )));
        }

        $req = $this->getRequest();
        $newAssetIds = $req->get('assets'); // $newAssets must be subset of $assets, and order may be changed
        
        $em = $this->getDoctrine()->getEntityManager();
        $assetIds = $hot->getAssetIds();
        $delAssetIds = empty($newAssetIds) ? $assetIds : array_diff($assetIds, $newAssetIds);
        if (!empty($delAssetIds)) {
            $assetRepo = $em->getRepository('MagendAssetBundle:Asset');
            foreach ($delAssetIds as $assetId) {
                $asset = $assetRepo->find($assetId);
                if ($asset) {
                    $em->remove($asset);
                }
            }
        }
        $hot->setAssetIds($newAssetIds);
        $em->flush();
        
        return new Response('{"success":1}');
    }
}
