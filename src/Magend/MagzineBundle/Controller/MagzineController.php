<?php

namespace Magend\MagzineBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Magend\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Magend\MagzineBundle\Entity\Magzine;
use Symfony\Component\HttpFoundation\Cookie;
use Doctrine\ORM\EntityRepository;

/**
 * 
 * @Route("/magzine")
 * @author kail
 */
class MagzineController extends Controller
{
    /**
     * @Route("/{id}/copyright", name="magzine_copyright", requirements={"id"="\d+"})
     * @Template()
     */    
    public function copyrightAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendMagzineBundle:Magzine');
        $magzine = $repo->find($id);
        if (!$magzine) {
            throw new \ Exception('Magzine not found');
        }
        
        $req = $this->getRequest();
        if ($req->getMethod() == 'POST') {
            echo $req->get('copyright');exit;
        }
        //$cprs = $magzine->getCopyrightArticles();
        return array(
            'magzine' => $magzine
        );
    }
    
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
     * @Route("/{id}/issues", name="magzine_issues", requirements={"id"="\d+"})
     * @Template()
     */
    public function issuesAction($id)
    {
        $cls = 'MagendIssueBundle:Issue';
        $em = $this->getDoctrine()->getEntityManager();
        $query = $em->createQuery("SELECT s FROM $cls s INDEX BY s.id WHERE s.magzine = :magId ORDER BY s.createdAt DESC")
                    ->setParameter('magId', $id);
        if ($this->getRequest()->isXmlHTTPRequest()) {
            $response = $this->container->get('templating')->renderResponse(
                'MagendMagzineBundle:Magzine:issueOptions.html.twig',
                array('issues' => $query->getResult()));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        $arr = $this->getList($cls, $query);
        $arr['issues'] = $arr['entities'];
        unset($arr['entities']);
        
        $repo = $this->getDoctrine()->getRepository('MagendMagzineBundle:Magzine');
        $arr['magzines'] = $repo->findAll();
        
        $response = $this->container->get('templating')->renderResponse(
            'MagendMagzineBundle:Magzine:issues.html.twig',
            $arr
        );
        $response->headers->setCookie(new Cookie('magzine_id', $id, time() + (3600 * 30 * 24)));
        return $response;
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
     * 
     * @Route("/{id}/del", name="magzine_del", requirements={"id"="\d+"})
     * @param int $id
     */
    public function delAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendMagzineBundle:Magzine');
        $magzine = $repo->find($id);
        if (!$magzine) {
            throw new \ Exception('Magzine not found');
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($magzine);
        $em->flush();
        
        return new RedirectResponse($this->generateUrl('magzine_list'));
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
        $form = $formBuilder->add('name', null, array('label' => '杂志分类'))
                            ->add('landscapeCoverImage', 'file', array('label' => '横版封面', 'required' => false))
                            ->add('portraitCoverImage', 'file', array('label' => '竖版封面', 'required' => false))
                            ->getForm();
        
        $req = $this->getRequest();
        if ($req->getMethod() == 'POST') {
            $form->bindRequest($req);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                
                // make it dirty for persistence
                if ($magzine->landscapeCoverImage || $magzine->portraitCoverImage) {
                    $magzine->setUpdatedAt(new \DateTime);
                }
                
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
