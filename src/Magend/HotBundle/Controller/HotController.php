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
     * @Route("/test", name="hot_test")
     * @Template()
     */
    public function testAction()
    {
        $hot = new Hot();
        $formBuilder = $this->createFormBuilder($hot);
        // $vars = get_object_vars($hot->stdProperty);
        // print_r($vars);exit;
        $form = $formBuilder->add('type', null, array('label' => 'type'))
                            ->add('mode', null, array('label' => 'mode', 'attr' => array('class' => 'fk')))
                            ->add('stdProperty', 'dynamic', array('label' => 'dynamic'))
                            ->getForm();
        // $form = $this->createForm(new DynamicType(), $hot->stdProperty);
        
        $req = $this->getRequest();
        if ($req->getMethod() == 'POST') {
            $form->bindRequest($req);
            if ($form->isValid()) {
                echo 'yes';exit;
                // return $this->redirect($this->generateUrl('magzine_list'));
            }
        }
        
        return array(
                'form' => $form->createView()
        );
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
