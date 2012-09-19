<?php

namespace Magend\NewsBundle\Controller;

use Exception;
use Magend\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Magend\NewsBundle\Entity\News;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * 
 * @Route("/news")
 * @author Kail
 */
class NewsController extends Controller
{
    /**
     * 
     * @Route("/list", name="news_list")
     * @Template()
     */
    public function listAction()
    {
        $arr = $this->getList('MagendNewsBundle:News');
        $arr['newsList'] = $arr['entities'];
        unset($arr['entities']);
        return $arr;
    }
    
    /**
     * 
     * @Route("/latest", name="news_latest")
     * @Template()
     */
    public function latestAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $dql = 'SELECT n FROM MagendNewsBundle:News n ORDER BY n.createdAt DESC';
        $q = $em->createQuery($dql)->setMaxResults(10);
        $newsList = $q->getResult();
        
        return array(
            'newsList' => $newsList
        );
    }
    
    /**
     *
     * @Route("/{id}/del", name="news_del", requirements={"id"="\d+"})
     * @param int $id
     */
    public function delAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendNewsBundle:News');
        $news = $repo->find($id);
        if (!$news) {
            throw new Exception('news not found');
        }
    
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($news);
        $em->flush();
    
        return new RedirectResponse($this->generateUrl('news_list'));
    }
    
    /**
     * @Route("/new", name="news_new")
     * @Template()
     */
    public function newAction()
    {
        return $this->submit();
    }
    
    /**
     *
     * @Route("/{id}/edit", name="news_edit", requirements={"id"="\d+"})
     * @Template("MagendNewsBundle:News:new.html.twig")
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
        $news = null;
        if (is_null($id)) {
            $news = new News();
        } else {
            $repo = $this->getDoctrine()->getRepository('MagendNewsBundle:News');
            $news = $repo->find($id);
            if (!$news) {
                throw new Exception('news not found');
            }
        }
    
        $formBuilder = $this->createFormBuilder($news);
        $form = $formBuilder->add('title', null, array('label' => '标题'))
                            ->add('url', null, array('label' => '网址', 'required' => false))
                            ->add('content', null, array('label' => '内容', 'required' => false))
                            ->getForm();
        
        if ($this->getRequest()->getMethod() == 'POST') {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($news);
                $em->flush();
    
                return $this->redirect($this->generateUrl('news_list'));
            }
        }
    
        return array(
                'news' => $news,
                'form' => $form->createView()
        );
    }
}
