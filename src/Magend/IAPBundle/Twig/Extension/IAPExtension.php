<?php

namespace Magend\IAPBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * IAPExtension
 * 
 * @author kail
 */
class IAPExtension extends \Twig_Extension
{
    private $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'getNumberOfWaitingIAP' => new \Twig_Function_Method($this, 'getNumberOfWaitingIAP'),
        );
    }
    
    public function getNumberOfWaitingIAP()
    {
        $em = $this->container->get('doctrine')->getEntityManager();
        $dql = 'SELECT COUNT(i.id) FROM MagendIAPBundle:IAP i LEFT JOIN i.issue s WHERE s.iapId IS NULL';
        $query = $em->createQuery($dql);
        return $query->getSingleScalarResult();
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'mag_iap';
    }
}
