<?php

namespace Magend\ProjectBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ProjectController extends Controller
{
    /**
     * @Route("/projects", name="proj_list")
     * @Template()
     */
    public function indexAction()
    {
        $projects = array('居住', '办公', '文教', '医疗', '商业', '体育', '交通', '园林', '纪念性', '工业', '农业', '其他');
        return array('projects' => $projects);
    }
}