<?php

namespace Magend\MagzineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Magend\MagzineBundle\Entity\Magzine
 *
 * @ORM\Table(name="mag_magzine")
 * @ORM\Entity(repositoryClass="Magend\MagzineBundle\Entity\MagzineRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Magzine
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string $landscape_cover
     *
     * @ORM\Column(name="landscape_cover", type="string", length=255, nullable=true)
     */
    private $landscapeCover;

    /**
     * @var string $portrait_cover
     *
     * @ORM\Column(name="portrait_cover", type="string", length=255, nullable=true)
     */
    private $portraitCover;

    /**
     * @var datetime $created_at
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var datetime $updated_at
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;
    
    /**
     * 
     * @var File
     */
    public $landscapeCoverImage;
    
    /**
     * 
     * @var File
     */
    public $portraitCoverImage;


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
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preFlush()
    {
        $now = new \DateTime;
        if (null === $this->createdAt) {
            $this->createdAt = $now;
        } else {
            $this->updatedAt = $now;
        }
        
        if ($this->landscapeCoverImage) {
            $imgName = uniqid('mag_') . '.' . $this->landscapeCoverImage->guessExtension();
            $this->landscapeCoverImage->move(__DIR__.'/../../../../web/uploads/', $imgName);
            
            if ($this->getLandscapeCover()) {
                @unlink(__DIR__.'/../../../../web/uploads/' . $this->getLandscapeCover());
            }
            
            $this->setLandscapeCover($imgName);
        }
        
        if ($this->portraitCoverImage) {
            $imgName = uniqid('mag_') . '.' . $this->portraitCoverImage->guessExtension();
            $this->portraitCoverImage->move(__DIR__.'/../../../../web/uploads/', $imgName);
            
            if ($this->getPortraitCover()) {
                @unlink(__DIR__.'/../../../../web/uploads/' . $this->getPortraitCover());
            }
            
            $this->setPortraitCover($imgName);
        }
    }
    
    /**
     * 
     * @ORM\PostRemove()
     */
    public function postRemove()
    {
        if ($this->getLandscapeCover()) {
            @unlink(__DIR__.'/../../../../web/uploads/' . $this->getLandscapeCover());
        }
        if ($this->getPortraitCover()) {
            @unlink(__DIR__.'/../../../../web/uploads/' . $this->getPortraitCover());
        }
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

    /**
     * Set landscape_cover
     *
     * @param string $landscapeCover
     */
    public function setLandscapeCover($landscapeCover)
    {
        $this->landscapeCover = $landscapeCover;
    }

    /**
     * Get landscape_cover
     *
     * @return string 
     */
    public function getLandscapeCover()
    {
        return $this->landscapeCover;
    }

    /**
     * Set portrait_cover
     *
     * @param string $portraitCover
     */
    public function setPortraitCover($portraitCover)
    {
        $this->portraitCover = $portraitCover;
    }

    /**
     * Get portrait_cover
     *
     * @return string 
     */
    public function getPortraitCover()
    {
        return $this->portraitCover;
    }

    /**
     * Set created_at
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get created_at
     *
     * @return datetime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updated_at
     *
     * @param datetime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get updated_at
     *
     * @return datetime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}