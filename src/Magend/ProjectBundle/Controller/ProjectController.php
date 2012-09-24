<?php

namespace Magend\ProjectBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Magend\ProjectBundle\Entity\Project;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * 
 * @Route("/project")
 * @author kail
 */
class ProjectController extends Controller
{
    /**
     * @Route("/list", name="proj_list")
     * @Template()
     */
    public function listAction()
    {
        $projectRepo = $this->getDoctrine()->getRepository('MagendProjectBundle:Project');
        $projects = $projectRepo->findAll();
        
        if ($this->getRequest()->get('_route') == '_internal') {
            return $this->get('templating')->renderResponse(
                'MagendProjectBundle:Project:_list.html.twig',
                array('projects' => $projects)
            );
        }
        return array('projects' => $projects);
    }

    /**
     * @Route("/add_default", name="proj_add_default")
     */
    public function initAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $defaultProjects = array('居住', '办公', '文教', '医疗', '商业', '体育', '交通', '园林', '纪念性', '工业', '农业', '其他');
        $projectRepo = $this->getDoctrine()->getRepository('MagendProjectBundle:Project');
        $projects = $projectRepo->findBy(array(
            'name' => $defaultProjects
        ));
        $existNames = array();
        foreach ($projects as $project) {
            $existNames[] = $project->getName();
        }
        
        foreach ($defaultProjects as $defaultProject) {
            if (in_array($defaultProject, $existNames)) {
                continue;
            }
            $project = new Project();
            $project->setName($defaultProject);
            $em->persist($project);
        }
        
        $em->flush();
        return new RedirectResponse($this->generateUrl('proj_list'));
    }
    
    /**
     * 
     * @Route("/{id}/del", name="proj_del", requirements={"id"="\d+"})
     * @param int $id
     */
    public function delAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendProjectBundle:Project');
        $project = $repo->find($id);
        if (!$project) {
            throw new \ Exception('Project not found');
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($project);
        $em->flush();
        
        return new RedirectResponse($this->generateUrl('proj_list'));
    }
    
    /**
     * @Route("/new", name="proj_new")
     * @Template()
     */
    public function newAction()
    {
        return $this->submit();
    }
    
    /**
     * 
     * @Route("/{id}/edit", name="proj_edit", requirements={"id"="\d+"})
     * @Template("MagendProjectBundle:Project:new.html.twig")
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
        $project = null;
        if (is_null($id)) {
            $project = new Project();
        } else {
            $repo = $this->getDoctrine()->getRepository('MagendProjectBundle:Project');
            $project = $repo->find($id);
            if (!$project) {
                throw new \ Exception('Project not found');
            }
        }
        
        $formBuilder = $this->createFormBuilder($project);
        $form = $formBuilder->add('name', null, array('label' => '项目类型'))
                            ->add('thumbnailImage', 'file', array('label' => '缩略图', 'required' => false))
                            ->getForm();
        
        $req = $this->getRequest();
        if ($req->getMethod() == 'POST') {
            $form->bindRequest($req);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                
                // make it dirty for persistence
                if ($project->thumbnailImage) {
                    $project->setUpdatedAt(new \DateTime);
                }
                
                $em->persist($project);
                $em->flush();
                
                return $this->redirect($this->generateUrl('proj_list'));
            }
        }
        
        return array(
            'project' => $project,
            'form' => $form->createView()
        );
    }
}