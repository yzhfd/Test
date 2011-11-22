<?php

namespace Magend\ArchitectBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * Magend\ArchitectBundle\Entity\Architect
 *
 * @ORM\Table(name="mag_architect")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Magend\ArchitectBundle\Entity\ArchitectRepository")
 */
class Architect
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;
    
    /**
     * 
     * 
     * @ORM\ManyToMany(targetEntity="Magend\ArticleBundle\Entity\Article", mappedBy="architects")
     */
    private $articles;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     *  
     * @param string $name
     */
    public function __construct($name = null)
    {
        $this->articles = new ArrayCollection();
        $this->setName($name);
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
    
    public function __toString()
    {
        return $this->name;
    }
}