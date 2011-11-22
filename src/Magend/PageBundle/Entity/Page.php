<?php

namespace Magend\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    /**
     * Set landscapeImg
     *
     * @param string $landscapeImg
     */
    public function setLandscapeImg($landscapeImg)
    {
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
     * @param Magend\ArticleBundle\Entity\Article $article
     */
    public function setArticle(\Magend\ArticleBundle\Entity\Article $article)
    {
        $this->article = $article;
    }

    /**
     * Get article
     *
     * @return Magend\ArticleBundle\Entity\Article 
     */
    public function getArticle()
    {
        return $this->article;
    }
}