<?php

namespace Magend\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Magend\ArticleBundle\Entity\Article;

/**
 * Magend\PageBundle\Entity\Page
 *
 * @ORM\Table(name="mag_page")
 * @ORM\Entity(repositoryClass="Magend\PageBundle\Entity\PageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Page
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
     * @var Article
     * 
     * @ORM\ManyToOne(targetEntity="Magend\ArticleBundle\Entity\Article", inversedBy="pages")
     */
    private $article;
    
    /**
     * @var ArrayCollection
     * 
     * 
     * @ORM\OneToMany(
     *     targetEntity="Magend\HotBundle\Entity\Hot",
     *     mappedBy="page",
     *     indexBy="id",
     *     cascade={"persist", "remove"},
     *     fetch="EXTRA_LAZY"
     * )
     */
    private $hots;
    
    /**
     * 
     * @var string
     * @ORM\Column(name="label", type="string", length=255, nullable=true)
     */
    private $label;
    
    /**
     * 
     * @var string
     * @ORM\Column(name="landscape_img", type="string", length=255, nullable=true)
     */
    private $landscapeImg;
    
    /**
     * 
     * @var serialized array
     * @ORM\Column(name="landscape_hots", type="text", nullable=true)
     */
    private $landscapeHots;
    
    /**
     * 
     * @var string
     * @ORM\Column(name="portrait_img", type="string", length=255, nullable=true)
     */
    private $portraitImg;
    
    /**
     * 
     * @var serialized array
     * @ORM\Column(name="portrait_hots", type="text", nullable=true)
     */
    private $portraitHots;

    /**
     * @var datetime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var datetime $updatedAt
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;
    
    /**
     * The image file
     * 
     * @var UploadedFile
     */
    public $file;

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
        $this->hots = new ArrayCollection();
    }
    
    /**
     * 
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function prePersist()
    {
        $now = new \DateTime;
        if (null === $this->createdAt) {
            $this->createdAt = $now;
        } else {
            $this->updatedAt = $now;
        }
    }
    
    /**
     * @ORM\PostRemove()
     */
    public function removeImgs()
    {
        if ($this->getLandscapeImg() != null) {
            @unlink(__DIR__.'/../../../../web/uploads/' . $this->getLandscapeImg());
        }
        
        if ($this->getPortraitImg() != null) {
            @unlink(__DIR__.'/../../../../web/uploads/' . $this->getPortraitImg());
        }
    }

    /**
     * Set landscapeImg
     *
     * @param string $landscapeImg
     */
    public function setLandscapeImg($landscapeImg)
    {
        if ($this->getLandscapeImg() != null && $this->getLandscapeImg() != $landscapeImg) {
            @unlink(__DIR__.'/../../../../web/uploads/' . $this->getLandscapeImg());
        }
        $this->landscapeImg = $landscapeImg;
    }

    /**
     * Get landscapeImg
     *
     * @return string 
     */
    public function getLandscapeImg()
    {
        return $this->landscapeImg;
    }

    /**
     * Set landscapeHots
     *
     * @param text $landscapeHots
     */
    public function setLandscapeHots($landscapeHots)
    {
        $this->landscapeHots = $landscapeHots;
    }

    /**
     * Get landscapeHots
     *
     * @return text 
     */
    public function getLandscapeHots()
    {
        return $this->landscapeHots;
    }

    /**
     * Set portraitImg
     *
     * @param string $portraitImg
     */
    public function setPortraitImg($portraitImg)
    {
        if ($this->getPortraitImg() != null && $this->getPortraitImg() != $portraitImg) {
            @unlink(__DIR__.'/../../../../web/uploads/' . $this->getPortraitImg());
        }
        $this->portraitImg = $portraitImg;
    }

    /**
     * Get portraitImg
     *
     * @return string 
     */
    public function getPortraitImg()
    {
        return $this->portraitImg;
    }

    /**
     * Set portraitHots
     *
     * @param text $portraitHots
     */
    public function setPortraitHots($portraitHots)
    {
        $this->portraitHots = $portraitHots;
    }

    /**
     * Get portraitHots
     *
     * @return text 
     */
    public function getPortraitHots()
    {
        return $this->portraitHots;
    }

    /**
     * Set article
     *
     * @param Article $article
     */
    public function setArticle($article)
    {
        $this->article = $article;
    }

    /**
     * Get article
     *
     * @return Article 
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * Set label
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Get label
     *
     * @return string 
     */
    public function getLabel()
    {
        return $this->label;
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
    
    public function getHots()
    {
        return $this->hots;
    }
    
    public function setHots($hots)
    {
        $this->hots = $hots;
    }
}