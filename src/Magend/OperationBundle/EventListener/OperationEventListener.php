<?php

namespace Magend\OperationBundle\EventListener;

use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Magend\MagazineBundle\Entity\Magazine;
use Magend\IssueBundle\Entity\Issue;
use Magend\ArticleBundle\Entity\Article;
use Magend\OperationBundle\Entity\Operation;

/**
 * OperationEventListener
 * 
 * @author kail
 */
class OperationEventListener
{
    private $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $router = $this->container->get('router');
        $op = null;
        if ($entity instanceof Magazine) {
            $op = new Operation();
            $op->setUser($user);
            $url = $router->generate('magazine_issues', array('id' => $entity->getId()));
            $op->setContent('添加了杂志<a href="' . $url . '">' . $entity->getName() .  '</a>');
        } else if ($entity instanceof Issue) {
            $op = new Operation();
            $op->setUser($user);
            $url = $router->generate('issue_article_list', array('id' => $entity->getId()));
            $op->setContent('添加了期刊<a href="' . $url . '">' . $entity->getTitle() .  '</a>');
        } else if ($entity instanceof Article) {
            $op = new Operation();
            $op->setUser($user);
            $url = $router->generate('article_edit', array('id' => $entity->getId()));
            $op->setContent('添加了文章<a href="' . $url . '">' . $entity->getTitle() .  '</a>');
        }
        if ($op) {
            $em->persist($op);
            $em->flush();
        }
    }
    
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $router = $this->container->get('router');
        $op = null;
        if ($entity instanceof Magazine) {
            $op = new Operation();
            $op->setUser($user);
            $url = $router->generate('magazine_issues', array('id' => $entity->getId()));
            $op->setContent('编辑了杂志<a href="' . $url . '">' . $entity->getName() .  '</a>');
        } else if ($entity instanceof Issue) {
            $op = new Operation();
            $op->setUser($user);
            $url = $router->generate('issue_article_list', array('id' => $entity->getId()));
            $op->setContent('编辑了期刊<a href="' . $url . '">' . $entity->getTitle() .  '</a>');
        } else if ($entity instanceof Article) {
            $op = new Operation();
            $op->setUser($user);
            $url = $router->generate('article_edit', array('id' => $entity->getId()));
            $op->setContent('编辑了文章<a href="' . $url . '">' . $entity->getTitle() .  '</a>');
        }
        if ($op) {
            $em->persist($op);
            $em->flush();
        }
    }
    
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        $router = $this->container->get('router');
        $op = null;
        if ($entity instanceof Magazine) {
            $op = new Operation();
            $op->setUser($user);
            $op->setContent('删除了杂志<strong>' . $entity->getName() .  '</strong>');
        } else if ($entity instanceof Issue) {
            $op = new Operation();
            $op->setUser($user);
            $op->setContent('删除了期刊<strong>' . $entity->getTitle() .  '</strong>');
        } else if ($entity instanceof Article) {
            $op = new Operation();
            $op->setUser($user);
            $op->setContent('删除了文章<stong>' . $entity->getTitle() .  '</strong>');
        }
        if ($op) {
            $em->persist($op);
        }
    }
}