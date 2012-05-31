<?php

namespace Magend\ArticleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Magend\ArticleBundle\Entity\Fav;
use Symfony\Component\HttpFoundation\Response;

/**
 * FavController
 * 
 * @author kail
 * @Route("/fav")
 */
class FavController extends Controller
{
    /**
     *  List articles faved by the user
     * 
     * @Route("/user/{id}/articles", name="article_fav_list", requirements={"id"="\d+"})
     * @Template()
     */
    public function listAction($id)
    {
        $dql = 'SELECT f, a FROM MagendArticleBundle:Fav f LEFT JOIN f.article a WHERE f.user = :user';
        $em = $this->getDoctrine()->getEntityManager();
        $q = $em->createQuery($dql)->setParameter('user', $id);
        $favs = $q->getResult();
        
        $tplVars = array(
            'favs' => $favs
        );
        if ($this->getRequest()->get('_format') == 'xml') {
            return $this->container->get('templating')->renderResponse('MagendArticleBundle:Fav:list.xml.twig', $tplVars);
        }
        return $tplVars;
    }
    
    /**
     * New fav
     * 
     * @Route("/article/{id}", name="article_fav_new", requirements={"id"="\d+"})
     */
    public function newAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendUserBundle:User');
        $uid = $this->getRequest()->get('uid');
        if ($uid === null) {
            return new Response(0);
        }
        $user = $repo->find($uid);
        $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Article');
        $article = $repo->find($id);
        
        $fav = new Fav();
        $fav->setArticle($article);
        $fav->setUser($user);
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($fav);
        $em->flush();
        
        return new Response(1);
    }
    
    // @Method("POST")
    
    /**
     * Delete fav
     *
     * @Route("/{id}/del", name="article_fav_del", requirements={"id"="\d+"})
     * 
     */
    public function delAction($id)
    {
        $repo = $this->getDoctrine()->getRepository('MagendArticleBundle:Fav');
        $fav = $repo->find($id);
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($fav);
        $em->flush();
        
        return new Response(1);
    }
}
