<?php

namespace Magend\ArticleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Magend\ArticleBundle\Entity\Comment;

/**
 * CommentController
 * 
 * @author kail
 * @Route("/comment")
 */
class CommentController extends Controller
{
    /**
     * // , requirements={"id"="\d+"}
     * 
     * @Route("/list", name="comment_list")
     * @Template()
     */
    public function listAction()
    {
        return array(
            'comments' => array()
        );
    }
    
    /**
     * New comment
     * 
     * @Route("/new", name="comment_new")
     * @Template()
     */
    public function newAction()
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
                $comment->setUser($user);
                $em->persist($comment);
                $em->flush();
                
                return $this->redirect($this->generateUrl('comment_list'));
            }
        }
        
        return array(
            'form' => $form->createView()
        );
    }
}
