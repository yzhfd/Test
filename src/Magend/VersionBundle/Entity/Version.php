<?php

namespace Magend\VersionBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Magend\VersionBundle\Entity\Version
 *
 * @ORM\Table(name="mag_version")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Version
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
     * @var string $target
     *
     * @ORM\Column(name="target", type="string")
     */
    private $target; 

    /**
     * @var integer $version
     *
     * @ORM\Column(name="version", type="integer")
     */
    private $version = 1;

    /**
     * @var datetime $updatedAt
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;


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
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        $this->updatedAt = new DateTime();
    }
    
    /**
     * Set version
     *
     * @param integer $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }
    
    public function incVersion()
    {
        ++$this->version;
    }

    /**
     * Get version
     *
     * @return integer 
     */
    public function getVersion()
    {
        return $this->version;
    }
    
    public function getTarget()
    {
        return $this->target;
    }
    
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * Set updatedAt
     *
     * @param datetime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get updatedAt
     *
     * @return datetime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
    
    public function __toString()
    {
        return $this->getVersion() . '';
    }
}