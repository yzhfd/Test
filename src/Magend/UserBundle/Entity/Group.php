<?php

namespace Magend\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\Group as AbstractedGroup;

/**
 * 
 * @ORM\Table(name="mag_group")
 * @ORM\Entity
 * @author kail
 */
class Group extends AbstractedGroup
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function __toString()
    {
        return $this->getName();
    }
}