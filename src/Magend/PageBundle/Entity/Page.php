<?php

namespace Magend\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Magend\ArticleBundle\Entity\Article;
use Magend\HotBundle\Entity\Hot;

/**
 * Magend\PageBundle\Entity\Page
 *
 * @ORM\Table(name="mag_page")
 * @ORM\Entity(repositoryClass="Magend\PageBundle\Entity\PageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Page
{
    const TYPE_MAIN      = 0;
    const TYPE_INFO      = 1;
    const TYPE_STRUCTURE = 2;
    
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
     * 
     * @var int
     * @ORM\Column(name="type", type="smallint")
     */
    private $type = self::TYPE_MAIN;
    
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
     * Keep track of the old image, which will be delete in preUpdate
     * 
     * @var string
     */
    private $oldLandscapeImg;
    
    /**
     * 
     * @var string
     * @ORM\Column(name="portrait_img", type="string", length=255, nullable=true)
     */
    private $portraitImg;
    
    /**
     * Keep track of the old image, which will be delete in preUpdate
     * 
     * @var string
     */
    private $oldPortraitImg;

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
    
    public function unlinkLandscapeImg()
    {
        if ($this->getLandscapeImg() != null) {
            @unlink(__DIR__.'/../../../../web/uploads/' . $this->getLandscapeImg());
        }
    }
    
    public function unlinkPortraitImg()
    {
        if ($this->getPortraitImg() != null) {
            @unlink(__DIR__.'/../../../../web/uploads/' . $this->getPortraitImg());
        }
    }
    
    /**
     * @ORM\PostRemove()
     */
    public function removeImgs()
    {
        $this->unlinkLandscapeImg();
        $this->unlinkPortraitImg();
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
    public function getLandscapeImg($withDir = true)
    {
        $article = $this->getArticle();
        if ($article) {
            $issue = $article->getIssue();
        }
        if ($issue) {
            $magzine = $issue->getMagzine();
        }
        return $withDir && $issue && $magzine ? $magzine->getId() . '/' . $issue->getId() . '/' .  $this->landscapeImg : $this->landscapeImg;
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
    
    /**
     * For the sake of simplicity
     * 
     * @param int $mode
     */
    private function _getHotsByMode($mode)
    {
        $modeHots = array();
        foreach ($this->hots as $hot) {
            if ($hot->getMode() == $mode) {
                $modeHots[] = $hot;
            }
        }
        
        return $modeHots;
    }
    
    public function getLandscapeHots()
    {
        return $this->_getHotsByMode(Hot::MODE_LANDSCAPE);
    }
    
    public function getPortraitHots()
    {
        return $this->_getHotsByMode(Hot::MODE_PORTRAIT);
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function setType($type)
    {
        $this->type = $type;
    }
}