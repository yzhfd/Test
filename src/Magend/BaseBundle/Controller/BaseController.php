<?php

namespace Magend\BaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;

class BaseController extends Controller
{
    /**
     * 
     * @param string $entityName
     * @param Query|QueryBuidler $q
     */
    public function getList($entityName, $q = null)
    {
        $em = $this->getDoctrine()->getEntityManager();
        if ($q == null) {
            $q = $em->createQueryBuilder()->select('e')->from($entityName, 'e')->orderBy('e.createdAt', 'desc');
        }
        $adapter = new DoctrineORMAdapter($q);
        $pager = new Pagerfanta($adapter);
        
        $page = $this->getRequest()->get('page', 1);
        $entities = array();
        try {
            $pager->setMaxPerPage(10);
            $pager->setCurrentPage($page);
            $entities = $pager->getCurrentPageResults();
        } catch (OutOfRangeCurrentPageException $e) {
            // simply no entities
        }
        
        return array(
            'pager' => $pager,
            'entities' => $entities
        );
    }
}
