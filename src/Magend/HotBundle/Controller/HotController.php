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
        $newAssets = $req->get('assets'); // $newAssets must be subset of $assets, and order may be changed
        if (empty($hot)) {
            return new Response(json_encode(array(
                'error' => 'request.no_asset'
            )));
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        $assetRepo = $em->getRepository('MagendAssetBundle:Asset');
        $assets = $hot->getAssets();
        $delAssets = array_diff($assets, $newAssets);
        if (!empty($delAssets)) {
            foreach ($delAssets as $asset) {
                $asset = $assetRepo->find($asset);
                if ($asset) {
                    $em->remove($asset);
                }
            }
        }
        $hot->setAssets($newAssets);
        $em->flush();
        
        return new Response('{"success":1}');
    }
}
