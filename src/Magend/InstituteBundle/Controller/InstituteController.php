<?php

namespace Magend\InstituteBundle\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Magend\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Magend\InstituteBundle\Entity\Institute;

/**
 * 
 * @Route("/institute")
 * @author kail
 */
class InstituteController extends Controller
{   
    /**
     * @Route("/list", name="institute_list")
     * @Template()
     */
    public function listAction()
    {
        $arr = $this->getList('MagendInstituteBundle:Institute');
        $arr['institutes'] = $arr['entities'];
        unset($arr['entities']);
        
        
        if ($this->getRequest()->get('_route') == '_internal') {
            return $this->get('templating')->renderResponse(
                'MagendInstituteBundle:Institute:_list.html.twig',
                $arr
            );
        }
        return $arr;
    }
    
    /**
     * 
     * @Route("/{id}/del", name="institute_del", requirements={"id"="\d+"})
     * @param int $id
     */
    public function delAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendInstituteBundle:Institute');
        $inst = $repo->find($id);
        if (!$inst) {
            throw new Exception('Institute not found');
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($inst);
        $em->flush();
        
        return new RedirectResponse($this->generateUrl('institute_list'));
    }
    
    /**
     * 
     * @Route("/new", name="institute_new")
     * @Template()
     */
    public function newAction()
    {
        return $this->submit();
    }
    
    /**
     * 
     * @Route("/{id}/edit", name="institute_edit", requirements={"id"="\d+"})
     * @Template("MagendInstituteBundle:Institute:new.html.twig")
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
        $institute = null;
        if (is_null($id)) {
            $institute = new Institute();
        } else {
            $repo = $this->getDoctrine()->getRepository('MagendInstituteBundle:Institute');
            $institute = $repo->find($id);
            if (!$institute) {
                throw new Exception('Institute not found');
            }
        }
        
        $formBuilder = $this->createFormBuilder($institute);
        $form = $formBuilder->add('name', null, array('label' => '机构名称'))
                            ->add('intro', null, array('label' => '简介'))
                            ->add('link', null, array('label' => '链接地址'))
                            ->add('thumbnailImage', 'file', array('label' => '缩略图', 'required' => false))
                            ->getForm();
        
        $req = $this->getRequest();
        if ($req->getMethod() == 'POST') {
            $form->bindRequest($req);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($institute);
                $em->flush();
                
                return $this->redirect($this->generateUrl('institute_list'));
            }
        }
        
        return array(
            'instituteId' => $id,
            'form' => $form->createView()
        );
    }
}
