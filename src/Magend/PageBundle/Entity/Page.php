<?php

namespace Magend\PageBundle\Entity;

use stdClass;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Magend\ArticleBundle\Entity\Article;
use Magend\HotBundle\Entity\Hot;
use Magend\HotBundle\Entity\HotContainer;

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
    
    public $hotsToRemove;
    
    /**
     * 
     * @var HotContainer
     */
    private $hotContainer;
    
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
     * @var string
     * @ORM\Column(name="landscape_img_thumbnail", type="string", length=255, nullable=true)
     */
    private $landscapeImgThumbnail;
    
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
     * 
     * @var string
     * @ORM\Column(name="portrait_img_thumbnail", type="string", length=255, nullable=true)
     */
    private $portraitImgThumbnail;
    
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
        $this->hotContainer = new HotContainer();
    }
    
    /**
     * 
     * @ORM\PostLoad()
     */
    public function postLoad()
    {
        $this->hotContainer = new HotContainer();
        foreach ($this->hots as $hot) {
            $this->hotContainer->addHot($hot);
        }
    }
    
    /**
     * 
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function prePersist()
    {
        $now = new DateTime;
        if (null === $this->createdAt) {
            $this->createdAt = $now;
        } else {
            $this->updatedAt = $now;
        }
    }
    
    public function unlinkLandscapeImg($withThumbnail = true)
    {
        if ($this->getLandscapeImg() != null) {
            @unlink(__DIR__.'/../../../../web/uploads/' . $this->getLandscapeImg());
        }
        if ($this->getLandscapeImgThumbnail() != null) {
            @unlink(__DIR__.'/../../../../web/uploads/' . $this->getLandscapeImgThumbnail());
        }
    }
    
    public function unlinkPortraitImg($withThumbnail = true)
    {
        if ($this->getPortraitImg() != null) {
            @unlink(__DIR__.'/../../../../web/uploads/' . $this->getPortraitImg());
        }
        if ($this->getPortraitImgThumbnail() != null) {
            @unlink(__DIR__.'/../../../../web/uploads/' . $this->getPortraitImgThumbnail());
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
    
    public function getType()
    {
        return $this->type;
    }
    
    public function setType($type)
    {
        $this->type = $type;
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
     * Set thumbnail of landscapeImg
     *
     * @return string 
     */
    public function setLandscapeImgThumbnail($landscapeImgThumbnail)
    {
        $this->landscapeImgThumbnail = $landscapeImgThumbnail;
    }
    
    public function getCustomLandscapeImgThumbnail()
    {
        return $this->landscapeImgThumbnail;
    }
    
    /**
     * Get thumbnail of landscapeImg
     *
     * @return string 
     */
    public function getLandscapeImgThumbnail()
    {
        if ($this->landscapeImgThumbnail == null && $this->getLandscapeImg() != null) {
            list($name, $ext) = explode('.', $this->getLandscapeImg());
            $thumbName = $name . "_thumb.$ext";
            if (file_exists(__DIR__.'/../../../../web/uploads/' . $thumbName)) {
                return $thumbName;
            }
        }
        return $this->landscapeImgThumbnail;
    }
    
    /**
     * Set thumbnail of portraitImg
     *
     * @return string 
     */
    public function setPortraitImgThumbnail($portraitImgThumbnail)
    {
        $this->portraitImgThumbnail = $portraitImgThumbnail;
    }   
    
    /**
     * Get thumbnail of portraitImg
     *
     * @return string 
     */
    public function getPortraitImgThumbnail()
    {
        if ($this->portraitImgThumbnail == null && $this->getPortraitImg() != null) {
            list($name, $ext) = explode('.', $this->getPortraitImg());
            $thumbName = $name . "_thumb.$ext";
            if (file_exists(__DIR__.'/../../../../web/uploads/' . $thumbName)) {
                return $thumbName;
            }
        }
        return $this->portraitImgThumbnail;
    }
    
    public function getCustomPortraitImgThumbnail()
    {
        return $this->portraitImgThumbnail;
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
        foreach ($hots as $hot) {
            $hot->setPage($this);
        }
        
        $this->hots = $hots;
    }
    
    /**
     * Add hot
     * 
     * @param Hot $hot
     */
    public function addHot($hot)
    {
        $hot->setPage($this);
        $this->hots[] = $hot;
        // $this->hotContainer->addHot($hot);
    }
    
    public function getHotContainer()
    {
        return $this->hotContainer;
    }
    
    public function setHotContainer($hotContainer)
    {
        $this->hotContainer = $hotContainer;
        $hots = $this->getHots();
        $this->hotsToRemove = array();
        foreach ($hots as $hot) {
            if (!$hotContainer->containsHot($hot)) {
                $this->hotsToRemove[] = $hot;
            }
        }
        $this->setHots($hotContainer->toHots());
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
}