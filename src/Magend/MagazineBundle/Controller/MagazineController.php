<?php

namespace Magend\MagazineBundle\Controller;

use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Magend\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Magend\MagazineBundle\Entity\Magazine;
use Symfony\Component\HttpFoundation\Cookie;
use Doctrine\ORM\EntityRepository;

/**
 * 
 * @Route("/magazine")
 * @author kail
 */
class MagazineController extends Controller
{   
    /**
     * @Route("/{id}/copyright", name="magazine_copyright", requirements={"id"="\d+"})
     * @Template()
     */    
    public function copyrightAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendMagazineBundle:Magazine');
        $magazine = $repo->find($id);
        if (!$magazine) {
            throw new Exception('Magazine not found');
        }
        
        $req = $this->getRequest();
        if ($req->getMethod() == 'POST') {
            $articleId = $req->get('copyright');
            $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
            $article = $repo->find($articleId);
            $magazine->setCopyrightArticle($article);
            $em = $this->getDoctrine()->getEntityManager();
            $em->flush();
            
            return new RedirectResponse($this->generateUrl('magazine_list'));
        }
        
        $cprs = $magazine->getCopyrightArticles();
        $noCopyrightArticle = empty($cprs) || $cprs->isEmpty();
        return array(
            'magazine'           => $magazine,
            'copyrightArticles'  => $cprs,
            'noCopyrightArticle' => $noCopyrightArticle
        );
    }
    
    /**
     * 
     * @Route("/{id}/classify", name="magazine_classify", requirements={"id"="\d+"})
     * @Template() 
     */
    public function classifyAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendMagazineBundle:Magazine');
        $magazine = $repo->find($id);
        return array('magazine' => $magazine);
    }
    
    /**
     * @Route("/list", name="magazine_list")
     * @Template()
     */
    public function listAction()
    {
        $q = null;
        $isAdmin = $this->get('security.context')->isGranted('ROLE_ADMIN');
        if (!$isAdmin) {
            $user = $this->get('security.context')->getToken()->getUser();
            $dql = 'SELECT m FROM MagendMagazineBundle:Magazine m LEFT JOIN m.staffUsers u WHERE m.owner = :user OR u = :user';
            $em = $this->getDoctrine()->getEntityManager();
            $q = $em->createQuery($dql)->setParameter('user', $user);
        }
        
        $arr = $this->getList('MagendMagazineBundle:Magazine', $q);
        $arr['magazines'] = $arr['entities'];
        unset($arr['entities']);
        return $arr;
    }
    
    /**
     * 
     * @Route("/{id}/issues", name="magazine_issues", requirements={"id"="\d+"})
     * @Template()
     */
    public function issuesAction($id)
    {
        $cls = 'MagendIssueBundle:Issue';
        $em = $this->getDoctrine()->getEntityManager();
        $query = $em->createQuery("SELECT s FROM $cls s INDEX BY s.id WHERE s.magazine = :magId ORDER BY s.createdAt DESC")
                    ->setParameter('magId', $id);
        $arr = $this->getList($cls, $query);
        $arr['issues'] = $arr['entities'];
        unset($arr['entities']);
        
        $repo = $this->getDoctrine()->getRepository('MagendMagazineBundle:Magazine');
        $mag = $repo->find($id);
        $arr['magazine'] = $mag;
        return $arr;
    }
    
    /**
     * 
     * @Route("/new", name="magazine_new")
     * @Template()
     */
    public function newAction()
    {
        $ret = $this->submit();
        if (is_array($ret)) {
            $em = $this->getDoctrine()->getEntityManager();
            $user = $this->get('security.context')->getToken()->getUser();
            $dql = 'SELECT COUNT(m.id) FROM MagendMagazineBundle:Magazine m WHERE m.owner = :user';
            $q = $em->createQuery($dql)->setParameter('user', $user);
            $ret['nbMags'] = $q->getSingleScalarResult();
        }
        
        return $ret;
    }
    
    /**
     * 
     * @Route("/{id}/edit", name="magazine_edit", requirements={"id"="\d+"})
     * @Template("MagendMagazineBundle:Magazine:new.html.twig")
     */
    public function editAction($id)
    {
        return $this->submit($id);
    }
    
    /**
     * 
     * @Route("/{id}/del", name="magazine_del", requirements={"id"="\d+"})
     * @param int $id
     */
    public function delAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendMagazineBundle:Magazine');
        $magazine = $repo->find($id);
        if (!$magazine) {
            throw new Exception('Magazine not found');
        }
        
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($magazine);
        $em->flush();
        
        $om = $this->get('magend.output_manager');
        $om->outputMagazinesXML();
        
        return new RedirectResponse($this->generateUrl('magazine_list'));
    }
    
    /**
     * Shared by new and edit
     * 
     * @param int $id
     * @throws Exception
     */
    private function submit($id = null)
    {
        $magazine = null;
        if (is_null($id)) {
            $magazine = new Magazine();
        } else {
            $repo = $this->getDoctrine()->getRepository('MagendMagazineBundle:Magazine');
            $magazine = $repo->find($id);
            if (!$magazine) {
                throw new Exception('Magazine not found');
            }
        }
        
        $formBuilder = $this->createFormBuilder($magazine);
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
                if ($magazine->landscapeCoverImage || $magazine->portraitCoverImage) {
                    $magazine->setUpdatedAt(new \DateTime);
                }
                
                if (is_null($id)) {
                    $vm = $this->get('magend.version_manager');
                    $vm->incGroupVersion();
                    $user = $this->get('security.context')->getToken()->getUser();
                    $magazine->setOwner($user);
                }
                
                $em->persist($magazine);
                $em->flush();
                
                return $this->redirect($this->generateUrl('magazine_list'));
            }
        }
        
        return array(
            'magazineId' => $id,
            'form' => $form->createView()
        );
    }
}
