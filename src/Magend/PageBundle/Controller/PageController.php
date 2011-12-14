<?php

namespace Magend\PageBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Magend\PageBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 *
 * @Route("/page")
 * @author kail
 */
class PageController extends Controller
{
    /**
     * @Route("/{id}/edit", name="page_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendPageBundle:Page');
        $page = $repo->find($id);

        return array(
            'page' => $page
        );
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
            
            // @todo remove from article.pageIds
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
     * @Route("/upload", name="page_upload", defaults={"_format" = "json"})
     * @Template()
     */
    public function uploadAction()
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

            // move it
            $rootDir = $this->container->getParameter('kernel.root_dir');
            $imgName = uniqid('page_') . '.' . $file->guessExtension();
            $file->move($rootDir . '/../web/uploads/', $imgName);
            
            $tplVars = array('page' => "uploads/$imgName");
            
            $articleId = $req->get('articleId');
            if ($articleId) {
                $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
                $article = $repo->find($articleId);
                $page = new Page();
                $page->setLandscapeImg($imgName);
                $page->setArticle($article);
                
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($page);
                $em->flush();
                
                $tplVars['id'] = $page->getId();
                $tplVars['delUrl'] = $this->generateUrl('page_del', array('id' => $page->getId()));
                $pageIds = $article->getPageIds();
                $pageIds[] = $page->getId();
                $article->setPageIds($pageIds);
                $em->flush();
            }
        }

        return $tplVars;
    }
}
