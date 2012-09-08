<?php

namespace Magend\PageBundle\Controller;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Magend\PageBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Magend\HotBundle\Entity\Hot;
use Magend\HotBundle\Entity\HotContainer;
use Magend\HotBundle\Form\Type\HotType;
use Magend\HotBundle\Form\Type\HotContainerType;
use Magend\IssueBundle\Util\SimpleImage;

/**
 *
 * @Route("/page")
 * @author kail
 */
class PageController extends Controller
{
    
    // @todo DRY create thumbnail code
    /**
     * 
     * @Route("/{id}/edit", name="page_edit", options={"expose" = true})
     * @Template()
     */
    public function editAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendPageBundle:Page');
        $page = $repo->find($id);
        
        $req = $this->getRequest();
        $hotContainer = $page->getHotContainer();
        $form = $this->createForm(new HotContainerType(), $hotContainer);
        if ($req->getMethod() == 'POST') {
            $form->bindRequest($req);
            if ($form->isValid()) {
                $page->setHotContainer($hotContainer);
                $pm = $this->get('magend.page_manager');
                $pm->updatePage($page);
                
                return $this->redirect($this->generateUrl('page_edit', array('id' => $id)));
            }
        }
        
        $pm = $this->get('magend.page_manager');
        return array(
            'hotdefs' => HotContainer::$hotsDefs,
            'page' => $page,
            'prev' => $pm->getPrevPage($page),
            'next' => $pm->getNextPage($page),
            'form' => $form->createView()
        );
    }
    
    /**
     * Replace the background image
     * 
     * @Route("/{id}/replace", name="page_replace", defaults={"_format" = "json"}, options={"expose" = true})
     */
    public function replaceAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendPageBundle:Page');
        $page = $repo->find($id);
        
        $file = $this->getRequest()->files->get('file');
        // move it
        $rootDir = $this->container->getParameter('kernel.root_dir');
        $imgName = uniqid('page_') . '.' . $file->guessExtension();
        $file->move($rootDir . '/../web/uploads/', $imgName);
        $page->setLandscapeImg($imgName);
        
        // create thumbnail
        $image = new SimpleImage();
        $image->load($rootDir . '/../web/uploads/' . $imgName);
        $imagineFilters = $this->container->getParameter('imagine.filters');
        list($width, $height) = $imagineFilters['landscapeThumb']['options']['size'];
        $image->resize($width, $height);
        list($uniqName, $ext) = explode('.', $imgName);
        $thumbName = $uniqName . "_thumb.$ext";
        $image->save($rootDir . '/../web/uploads/' . $thumbName);
        
        $page->setLandscapeImgThumbnail($thumbName);
        
        $em = $this->getDoctrine()->getEntityManager();
        $em->flush();
        
        return new Response(json_encode(array('img' => $imgName)));
    }
    
    /**
     * Change page's thumbnail
     * 
     * @todo landscape or thumbnail
     * @Route("/{id}/change-thumbnail", name="page_change_thumbnail", defaults={"_format" = "json"}, options={"expose" = true})
     */
    public function changeThumbnail($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendPageBundle:Page');
        $page = $repo->find($id);
        
        $file = $this->getRequest()->files->get('file');
        // move it
        $rootDir = $this->container->getParameter('kernel.root_dir');
        $tmpName = uniqid('tmp_') . '.' . $file->guessExtension();
        $file->move($rootDir . '/../web/uploads/', $tmpName);
        
        // create thumbnail
        $image = new SimpleImage();
        $image->load($rootDir . '/../web/uploads/' . $tmpName);
        $imagineFilters = $this->container->getParameter('imagine.filters');
        list($width, $height) = $imagineFilters['landscapeThumb']['options']['size'];
        $image->resize($width, $height);
        list($uniqName, $ext) = explode('.', substr($tmpName, 4));
        $thumbName = 'page_' . $uniqName . "_thumb.$ext";
        
        $image->save($rootDir . '/../web/uploads/' . $thumbName);
        @unlink($rootDir . '/../web/uploads/' . $tmpName);
        
        $page->setLandscapeImgThumbnail($thumbName);
        $em = $this->getDoctrine()->getEntityManager();
        $em->flush();
        
        return new Response(json_encode(array('img' => $thumbName)));
    }
    
    /**
     *
     * @Route("/{id}/delete", name="page_del", defaults={"_format" = "json"}) 
     */
    public function delAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendPageBundle:Page');
        $page = $repo->find($id);
        if ($page) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->remove($page);
            $em->flush();
        }
        
        return new Response('');
    }

    /**
     * Upload image if page not exist will create it
     * @todo landscape or portrait
     * 
     * Use request parameter TYPE to differentiate among different groups
     *
     * @Route("/upload/{type}", name="page_upload", defaults={"_format" = "json"}, requirements={"type"="[0-2]"})
     * @Template()
     */
    public function uploadAction($type)
    {
        $req = $this->getRequest();
        $tplVars = array(
            'page'   => null,
            'id'     => null,
            'delUrl' => null,
            'seq'    => null
        );
        
        if ($req->isXmlHTTPRequest() && ($req->getMethod() == 'POST' || $req->getMethod() == 'PUT')) {
            $file = $req->files->get('file');
            if (empty($file)) {
                return $tplVars;
            }

            $articleId = $req->get('articleId');
            if ($articleId === null) {
                throw new Exception('page.article_notfound');
            }
            $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
            $article = $repo->find($articleId);
            
            // move it
            $rootDir = $this->container->getParameter('kernel.root_dir');
            $imgName = uniqid('page_') . '.' . $file->guessExtension();
            $file->move($rootDir . '/../web/uploads', $imgName);
            $tplVars = array('page' => "uploads/$imgName");
            
            $page = new Page();
            $page->setSeq($req->get('seq', 0));
            $page->setLandscapeImg($imgName);
            
            // create thumbnail
            $image = new SimpleImage();
            $image->load($rootDir . '/../web/uploads/' . $imgName);
            $imagineFilters = $this->container->getParameter('imagine.filters');
            list($width, $height) = $imagineFilters['landscapeThumb']['options']['size'];
            $image->resize($width, $height);
            list($uniqName, $ext) = explode('.', $imgName);
            $thumbName = $uniqName . "_thumb.$ext";
            $image->save($rootDir . '/../web/uploads/' . $thumbName);
            
            $page->setLandscapeImgThumbnail($thumbName);
            
            $page->setArticle($article);
            $page->setType($type);
            
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($page);
            $em->flush();
            
            $tplVars['id'] = $page->getId();
            $tplVars['seq'] = $page->getSeq();
            $tplVars['delUrl'] = $this->generateUrl('page_del', array('id' => $page->getId()));
        }

        return $tplVars;
    }
    
    /**
     * Update seq of the page
     * 
     * @Route("/{id}/seq/{seq}", name="page_seq", defaults={"_format" = "json"}, options={"expose" = true}, requirements={"id"="\d+", "seq"="\d+"})
     */
    public function seqAction($id, $seq)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $repo = $this->getDoctrine()->getRepository('MagendPageBundle:Page');
        $page = $repo->find($id);
        $page->setSeq($seq);
        $em->flush();
        
        return new Response('');
    }
}
