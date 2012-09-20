<?php

namespace Magend\OperationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * 
 * @Route("/operation")
 * @author Kail
 */
class OperationController extends Controller
{
    /**
     * @Route("/list")
     * @Template()
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $dql = 'SELECT o, u FROM MagendOperationBundle:Operation o LEFT JOIN o.user u ORDER BY o.createdAt DESC';
        $q = $em->createQuery($dql)->setMaxResults(10);
        $operations = $q->getResult();
        
        return array('operations' => $operations);
    }
}
