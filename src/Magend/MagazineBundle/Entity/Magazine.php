<?php

namespace Magend\MagazineBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Magend\ArticleBundle\Entity\Article;
use Magend\UserBundle\Entity\User;

/**
 * Magend\MagazineBundle\Entity\Magazine
 *
 * @ORM\Table(name="mag_magazine")
 * @ORM\Entity(repositoryClass="Magend\MagazineBundle\Entity\MagazineRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Magazine
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
     * Owner, who is normally corp user
     * 
     * @var User
     * @ORM\ManyToOne(targetEntity="Magend\UserBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $owner;

    /**
     * Staff user who are permitted to edit this magazine
     *
     * @var User
     * @ORM\ManyToMany(targetEntity="Magend\UserBundle\Entity\User", mappedBy="grantedMags")
     */
    private $staffUsers;

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
     * @var Article $copyrightArticle
     *
     * @ORM\OneToOne(targetEntity="Magend\ArticleBundle\Entity\Article")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $copyrightArticle;
    
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
     * @var ArrayCollection
     * 
     * 
     * @ORM\OneToMany(
     *     targetEntity="Magend\IssueBundle\Entity\Issue",
     *     mappedBy="magazine",
     *     indexBy="id",
     *     cascade={"persist", "remove"},
     *     fetch="EXTRA_LAZY"
     * )
     * @ORM\OrderBy({"createdAt"="ASC"})
     */
    private $issues;
    
    /**
     * @var ArrayCollection
     * 
     * 
     * @ORM\OneToMany(
     *     targetEntity="Magend\ArticleBundle\Entity\Article",
     *     mappedBy="copyrightMagazine",
     *     fetch="EXTRA_LAZY"
     * )
     */
    private $copyrightArticles;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function __construct()
    {
        $this->issues = new ArrayCollection();
        $this->copyrightArticles = new ArrayCollection();
        $this->staffUsers = new ArrayCollection();
    }
    
    public function __toString()
    {
        return $this->name;
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
     * Get all issues
     * 
     * @return ArrayCollection
     */
    public function getIssues()
    {
        return $this->issues;
    }
    
    public function setIssues($issues)
    {
        $this->issues = new ArrayCollection($issues);
    }
    
    public function getNbIssues()
    {
        return $this->issues->count();
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

    /**
     * Set copyrightArticle
     *
     * @param string $copyrightArticle
     */
    public function setCopyrightArticle($copyrightArticle)
    {
        $this->copyrightArticle = $copyrightArticle;
    }

    /**
     * Get copyrightArticle
     *
     * @return string 
     */
    public function getCopyrightArticle()
    {
        return $this->copyrightArticle;
    }

    /**
     * Add issues
     *
     * @param Magend\IssueBundle\Entity\Issue $issues
     */
    public function addIssue(\Magend\IssueBundle\Entity\Issue $issues)
    {
        $this->issues[] = $issues;
    }

    /**
     * Add copyrightArticle
     *
     * @param Magend\ArticleBundle\Entity\Article $copyrightArticle
     */
    public function addCopyrightArticle(Article $copyrightArticle)
    {
        $this->copyrightArticles[] = $copyrightArticle;
    }

    /**
     * Get copyrightArticles
     *
     * @return ArrayCollection 
     */
    public function getCopyrightArticles()
    {
        return $this->copyrightArticles;
    }
    
    public function getOwner()
    {
        return $this->owner;
    }
    
    public function setOwner($user)
    {
        $this->owner = $user;
    }
    
    public function getStaffUsers()
    {
        return $this->staffUsers;
    }
    
    public function addStaffUser($user)
    {
        $this->staffUsers[] = $user;
    }
}