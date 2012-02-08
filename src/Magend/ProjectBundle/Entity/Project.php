<?php

namespace Magend\ProjectBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * Magend\ProjectBundle\Entity\Project
 *
 * @ORM\Table(name="mag_project")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="Magend\ProjectBundle\Entity\ProjectRepository")
 * @DoctrineAssert\UniqueEntity("name")
 */
class Project
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
     * @var string $thumbnail
     *
     * @ORM\Column(name="thumbnail", type="string", length=255, nullable=true)
     */
    private $thumbnail;
    
    /**
     * @var datetime $createdAt
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
    public $thumbnailImage;
    
    /**
     * 
     * 
     * @ORM\ManyToMany(targetEntity="Magend\ArticleBundle\Entity\Article", mappedBy="keywords")
     */
    private $articles;

    /**
     * 
     */
    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }
    
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
        
        if ($this->thumbnailImage) {
            $imgName = uniqid('proj_') . '.' . $this->thumbnailImage->guessExtension();
            $this->thumbnailImage->move(__DIR__.'/../../../../web/uploads/', $imgName);
            
            if ($this->getThumbnail()) {
                @unlink(__DIR__.'/../../../../web/uploads/' . $this->getThumbnail());
            }
            
            $this->setThumbnail($imgName);
        }
    }
    
    /**
     * 
     * @ORM\PostRemove()
     */
    public function postRemove()
    {
        if ($this->getThumbnail()) {
            @unlink(__DIR__.'/../../../../web/uploads/' . $this->getThumbnail());
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
     * Set createdAt
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return datetime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set thumbnail
     *
     * @param string $thumbnail
     */
    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;
    }

    /**
     * Get thumbnail
     *
     * @return string 
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
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

    /**
     * Add articles
     *
     * @param Magend\ArticleBundle\Entity\Article $articles
     */
    public function addArticle(\Magend\ArticleBundle\Entity\Article $articles)
    {
        $this->articles[] = $articles;
    }

    /**
     * Get articles
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getArticles()
    {
        return $this->articles;
    }
    
    public function __toString()
    {
        return $this->name;
    }
}