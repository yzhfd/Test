<?php

namespace Magend\PageBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Magend\PageBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Magend\HotBundle\Entity\Hot;
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
     * @Route("/test", name="page_test", options={"expose" = true})
     * @Template()
     */
    public function testAction()
    {
        $repo = $this->getDoctrine()->getRepository('MagendPageBundle:Page');
        $page = $repo->find(6);
        if (!$page) {
            $page = new Page();
        }
        
        $req = $this->getRequest();
        $hotContainer = $page->getHotContainer();
        $form = $this->createForm(new HotContainerType(), $hotContainer);
        if ($req->getMethod() == 'POST') {
            $form->bindRequest($req);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $page->setHotContainer($hotContainer);
                $em->persist($page);
                $em->flush();
                
                return $this->redirect($this->generateUrl('page_test'));
            }
        }
        
        return array(
                'form' => $form->createView()
        );
    }
    
    
    /**
     * 
     * @Route("/{id}/edit", name="page_edit", options={"expose" = true})
     * @Template()
     */
    public function editAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendPageBundle:Page');
        $page = $repo->find($id);
        
        $hots = $page->getHots();
        if (!empty($hots)) {
            $em = $this->getDoctrine()->getEntityManager();
            $dql = 'SELECT h, a FROM MagendHotBundle:Hot h LEFT JOIN h.assets a WHERE h in (:hots)';
            if (!is_array($hots) && method_exists($hots, 'toArray')) $hots = $hots->toArray();
            $hots = array_values($hots);
            if (!empty($hots)) {
                $q = $em->createQuery($dql)->setParameter('hots', $hots);
                $q->getResult();
            }
        }
        
        $pageIds = $page->getArticle()->getPageIds();
        $index = array_search($id, $pageIds);
        $prev = $next = null; // id of previous and next page
        if ($index !== false) {
            if (isset($pageIds[$index - 1])) {
                $prev = $pageIds[$index - 1];
            }
            if (isset($pageIds[$index + 1])) {
                $next = $pageIds[$index + 1];
            }
        }
        
        return array(
            'page' => $page,
            'prev' => $prev,
            'next' => $next
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
     * @Route("/{id}", name="backbone_page_del", defaults={"_format" = "json"})
     * @Method("delete")
     */
    public function backboneDelAction($id)
    {
        return new Response('xx'.$id);
    }
    
    /**
     *
     * @Route("/{id}/delete", name="page_del", defaults={"_format" = "json"})
     * 
     */
    public function delAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendPageBundle:Page');
        $page = $repo->find($id);
        if ($page) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->remove($page);
            
            $article = $page->getArticle();
            $pageIds = $article->getPageIds();
            foreach ($pageIds as $index=>$pageId) {
                if ($pageId == $page->getId()) {
                    unset($pageIds[$index]);
                    break;
                }
            }
            $article->setPageIds($pageIds);
            
            $em->flush();
        }
        
        return new Response('');
    }

    /**
     *
     * @Route("/{id}", name="page_update", defaults={"_format" = "json"}, requirements={"id"="\d+"})
     * @Method({"put", "post"})
     */
    public function updateAction($id)
    {
        return $this->forward('MagendPageBundle:Page:newUpdate');
    }

    /**
     *
     *
     * @Route("", name="page_new_update", defaults={"_format" = "json"})
     */
    public function newUpdateAction()
    {
        $req = $this->getRequest();
        if (!$req->isXmlHTTPRequest()) {
            throw new \ Exception("Not allowed to access this page");
        }

        $json = $req->getContent();
        $articleId = null;
        $paramsObj = json_decode($json);
        $em = $this->getDoctrine()->getEntityManager();
        if (isset($paramsObj->id)) {
            $repo = $this->getDoctrine()->getRepository('MagendPageBundle:Page');
            $page = $repo->find($paramsObj->id);
            $articleId = $page->getArticle()->getId();
        } else {
            $page = new Page();

            if (!isset($paramsObj->articleId)) {
                return new Response(json_encode(array(
                    'error' => 'Article Id is required'
                    )));
            }
        }

        if ($paramsObj->articleId != $articleId) {
            $articleId = $paramsObj->articleId;
            $articleRef = $em->getReference('MagendArticleBundle:Article', $articleId);
            $page->setArticle($articleRef);
        }

        // set
        if (isset($paramsObj->label)) {
            $page->setLabel($paramsObj->label);
        }
        if (isset($paramsObj->landscapeImg)) {
            $page->setLandscapeImg($paramsObj->landscapeImg);
        }
        if (isset($paramsObj->portraitImg)) {
            $page->setPortraitImg($paramsObj->portraitImg);
        }

        $em->persist($page);
        $em->flush();

        $response = json_encode(array(
            'id' => $page->getId()
        ));
        return new Response($response);
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
            'delUrl' => null
        );
        if ($req->isXmlHTTPRequest() && ($req->getMethod() == 'POST' || $req->getMethod() == 'PUT')) {
            $file = $req->files->get('file');
            if (empty($file)) {
                return $tplVars;
            }

            $articleId = $req->get('articleId');
            if ($articleId === null) {
                throw new \Exception('page.article_notfound');
            }
            $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
            $article = $repo->find($articleId);
            
            // move it
            $rootDir = $this->container->getParameter('kernel.root_dir');
            $imgName = uniqid('page_') . '.' . $file->guessExtension();
            $file->move($rootDir . '/../web/uploads', $imgName);
            $tplVars = array('page' => "uploads/$imgName");
            
            $page = new Page();
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
            $tplVars['delUrl'] = $this->generateUrl('page_del', array('id' => $page->getId()));
            $pageIds = $article->getPageIds($type);
            $pageIds[] = $page->getId();
            $article->setPageIds($pageIds, $type);
            $em->flush();
        }

        return $tplVars;
    }
    
    /**
     * 
     * Save hots
     * only position and dimension
     * 
     * @Route("/savehots", name="page_hots_save", defaults={"_format" = "json"}, options={"expose" = true})
     */
    public function saveHotsAction()
    {
        $req = $this->getRequest();
        
        $pageId = $req->get('id');
        $repo = $this->getDoctrine()->getRepository('MagendPageBundle:Page');
        $page = $repo->find($pageId);
        if (empty($page)) {
            throw new \ Exception('page not found');
        }
        
        // @todo landscape or portrait
        $hotEntities = array();
        $em = $this->getDoctrine()->getEntityManager();
        $hotRepo = $this->getDoctrine()->getRepository('MagendHotBundle:Hot');
        $hotIds = array();
        $hots = $req->get('hots');
        if (!empty($hots)) {
            foreach ($hots as $hot) {
                $hotEntity = null;
                if (isset($hot['id'])) {
                    $hotEntity = $hotRepo->find($hot['id']);
                }
                if (empty($hotEntity)) {
                    $hotEntity = new Hot();
                    $hotEntity->setType($hot['type']);
                    $hotEntity->setPage($page);
                    $em->persist($hotEntity);
                } else {
                    $hotIds[] = $hot['id'];
                }
                
                // extra attributes, specific to type
                if (isset($hot['extras'])) {
                    $hotEntity->setExtraAttrs($hot['extras']);
                    unset($hot['extras']);
                }
                $hotEntity->setAttrs($hot);
                
                $hotEntities[] = $hotEntity;
            }
        }
        
        $pageHots = $page->getLandscapeHots();
        foreach ($pageHots as $pageHot) {
            if (!in_array($pageHot->getId(), $hotIds)) {
                $em->remove($pageHot);
            }
        }
        $em->flush();
        
        $ret = array();
        foreach ($hotEntities as $hotEntity) {
            $ret[] = $hotEntity->getId();
        }
        if (empty($ret)) {
            $ret['success'] = 1;
        }
        return new Response(json_encode($ret));
    }
}
