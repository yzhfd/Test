<?php

namespace Magend\HomeBundle\EventListener;

use Exception;
use DateTime;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * KernelEventListener
 * 
 * @author kail
 */
class KernelEventListener
{
    private $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    // not loaded now, triggered many times on page request
    public function onKernelRequest(GetResponseEvent $event)
    {
        $route = $this->container->get('request')->get('_route');
        $reqType = $event->getRequestType();
        if ($reqType !== HttpKernelInterface::MASTER_REQUEST || $route == '_internal') {
            return;
        }
        
        $repo = $this->container->get('doctrine')->getRepository('MagendMagazineBundle:Magazine');
        $event->getRequest()->getSession()->set('globalMags', $repo->findAll());
    }
}