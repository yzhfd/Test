<?php

namespace Magend\VersionBundle\Entity;

use Symfony\Component\DependencyInjection\ContainerInterface;

class VersionManager
{
    private $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function incGroupVersion()
    {
        $groupVersion = $this->getGroupVersion();
        $groupVersion->incVersion();
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->flush();
    }
    
    public function incIssueVersion()
    {
        $issueVersion = $this->getIssueVersion();
        $issueVersion->incVersion();
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->flush();
    }
    
    public function getGroupVersion()
    {
        $repo = $this->container->get('doctrine')->getRepository('MagendVersionBundle:Version');
        $groupVersion = $repo->findOneBy(array(
            'target' => 'group'
        ));
        if (empty($groupVersion)) {
            $groupVersion = new Version();
            $groupVersion->setTarget('group');
            $em = $this->container->get('doctrine.orm.entity_manager');
            $em->persist($groupVersion);
            $em->flush();
        }
        return $groupVersion;
    }
    
    public function getIssueVersion()
    {
        $repo = $this->container->get('doctrine')->getRepository('MagendVersionBundle:Version');
        $issueVersion = $repo->findOneBy(array(
            'target' => 'issue'
        ));
        if (empty($issueVersion)) {
            $issueVersion = new Version();
            $issueVersion->setTarget('issue');
            $em = $this->container->get('doctrine.orm.entity_manager');
            $em->persist($issueVersion);
            $em->flush();
        }
        return $issueVersion;
    }
    
    public function getVersionFileContents()
    {
        $versionFileContents = $this->container->get('templating')->render(
            'MagendVersionBundle:Version:version.xml.twig',
            array(
                'groupVersion' => $this->getGroupVersion(),
                'issueVersion' => $this->getIssueVersion(),
            )
        );
        return $versionFileContents;
    }
}