<?php

namespace Magend\MagzineBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Magend\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Magend\MagzineBundle\Entity\Magzine;

/**
 * 
 * @Route("/magzine")
 * @author kail
 */
class MagzineController extends Controller
{
    /**
     * @Route("/list", name="magzine_list")
     * @Template()
     */
    public function listAction()
    {
        $arr = $this->getList('MagendMagzineBundle:Magzine');
        $arr['magzines'] = $arr['entities'];
        unset($arr['entities']);
        return $arr;
    }
    
    /**
     * 
     * @Route("/new", name="magzine_new")
     * @Template()
     */
    public function newAction()
    {
        return $this->submit();
    }
    
    /**
     * 
     * @Route("/{id}/edit", name="magzine_edit", requirements={"id"="\d+"})
     * @Template("MagendMagzineBundle:Magzine:new.html.twig")
     */
    public function editAction($id)
    {
        return $this->submit($id);
    }
    
    /**
     * Shared by new and edit
     * 
     * @param int $id
     * @throws Exception
     */
    private function submit($id = null)
    {
        $magzine = null;
        if (is_null($id)) {
            $magzine = new Magzine();
        } else {
            $repo = $this->getDoctrine()->getRepository('MagendMagzineBundle:Magzine');
            $magzine = $repo->find($id);
            if (!$magzine) {
                throw new \ Exception('Magzine not found');
            }
        }
        
        $formBuilder = $this->createFormBuilder($magzine);
        $form = $formBuilder->add('name', null, array('label' => '分类名称'))
                            ->add('landscapeCoverImage', 'file', array('label' => '横版封面', 'required' => false))
                            ->add('portraitCoverImage', 'file', array('label' => '竖版封面', 'required' => false))
                            ->getForm();
        
        $req = $this->getRequest();
        if ($req->getMethod() == 'POST') {
            $form->bindRequest($req);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($magzine);
                $em->flush();
                
                return $this->redirect($this->generateUrl('magzine_list'));
            }
        }
        
        return array(
            'magzineId' => $id,
            'form' => $form->createView()
        );
    }
}
