<?php

namespace Magend\ArticleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Magend\ArticleBundle\Entity\Comment;
use Symfony\Component\HttpFoundation\Response;

/**
 * CommentController
 * 
 * @author kail
 * @Route("/article")
 */
class CommentController extends Controller
{
    /**
     *  
     * 
     * @Route("/{id}/comment/list", name="article_comment_list", requirements={"id"="\d+"})
     * @Template()
     */
    public function listAction($id)
    {
        $dql = 'SELECT c, u FROM MagendArticleBundle:Comment c LEFT JOIN c.user u WHERE c.article = :article';
        $em = $this->getDoctrine()->getEntityManager();
        $q = $em->createQuery($dql)->setParameter('article', $id);
        $comments = $q->getResult();
        
        $tplVars = array(
            'comments' => $comments
        );
        if ($this->getRequest()->get('_format') == 'xml') {
            return $this->container->get('templating')->renderResponse('MagendArticleBundle:Comment:list.xml.twig', $tplVars);
        } else {
            $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
            $article = $repo->find($id);
            $tplVars['article'] = $article;
        }
        return $tplVars;
    }
    
    /**
     * New comment
     * 
     * @Route("/{id}/comment/new", name="article_comment_new", requirements={"id"="\d+"})
     * @Template()
     */
    public function newAction($id)
    {
        $comment = new Comment();
        
        $formBuilder = $this->createFormBuilder($comment);
        $form = $formBuilder->add('body', null, array('label' => '留言'))
                            ->getForm();
        
        $req = $this->getRequest();
        if ($req->getMethod() == 'POST') {
            $form->bindRequest($req);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $user = $this->get('security.context')->getToken()->getUser();
                $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
                $article = $repo->find($id);
                                
                $comment->setArticle($article);
                $comment->setUser($user);
                $em->persist($comment);
                $em->flush();
                
                return $this->redirect($this->generateUrl('article_comment_list', array('id' => $id)));
            }
        }
        
        return array(
            'form' => $form->createView()
        );
    }
    
    /**
     * Delete comment
     * 
     * @Route("/{id}/comment/del", name="article_comment_del", requirements={"id"="\d+"})
     */
    public function delAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Comment');
        $cmt = $repo->find($id);
        $article = $cmt->getArticle();
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($cmt);
        $em->flush();
        
        return $this->redirect($this->generateUrl('article_comment_list', array('id' => $article->getId())));
    }
    
    /**
     * New comment by API
     * 
     * @Route("/api/{id}/comment/new", name="article_api_comment_new", requirements={"id"="\d+"})
     * @Method("POST")
     */
    public function newApiAction($id)
    {
        $body = $this->getRequest()->get('body');
        $body = trim($body);
        if (empty($body)) return new Response(0);
        
        $repo = $this->getDoctrine()->getRepository('MagendUserBundle:User');
        $uid = $this->getRequest()->get('uid');
        if ($uid === null) {
            return new Response(0);
        }
        $user = $repo->find($uid);
        $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
        $article = $repo->find($id);
        
        $comment = new Comment();
        $comment->setBody($body);
        $comment->setArticle($article);
        $comment->setUser($user);
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($comment);
        $em->flush();
        
        return new Response(1);
    }
}
