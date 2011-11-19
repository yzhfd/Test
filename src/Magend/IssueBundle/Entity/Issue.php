<?php

namespace Magend\IssueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Magend\IssueBundle\Entity\Issue
 *
 * @ORM\Table(name="mag_issue")
 * @ORM\Entity(repositoryClass="Magend\IssueBundle\Entity\IssueRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Issue
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
     * This will be used to order issues
     * 
     * @var string $serial
     *
     * @ORM\Column(name="serial", type="string", length=255)
     */
    private $serial;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var integer $price_level
     *
     * @ORM\Column(name="price_level", type="integer", nullable=true)
     */
    private $priceLevel;

    /**
     * @var string $cover
     *
     * @ORM\Column(name="cover", type="string", length=255)
     */
    private $cover;

    /**
     * Serialized array of article ids
     * 
     * @var string $articles
     *
     * @ORM\Column(name="articles", type="text")
     */
    private $articles;

    /**
     * @var integer $nbArticles
     *
     * @ORM\Column(name="nb_articles", type="integer")
     */
    private $nbArticles = 0;

    /**
     * @var integer $nbPages
     *
     * @ORM\Column(name="nb_pages", type="integer")
     */
    private $nbPages = 0;

    /**
     * @var datetime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var datetime $updatedAt
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @var boolean $publish
     *
     * @ORM\Column(name="publish", type="boolean")
     */
    private $publish;

    /**
     * @var integer $publisher
     *
     * @ORM\Column(name="publisher", type="integer")
     */
    private $publisher;

    /**
     * @var integer $nbFaved
     *
     * @ORM\Column(name="nb_faved", type="integer")
     */
    private $nbFaved = 0;

    /**
     * @var integer $nbDownloaded
     *
     * @ORM\Column(name="nb_downloaded", type="integer")
     */
    private $nbDownloaded = 0;


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
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime;
    }
    
    /**
     * 
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime;
    }

    /**
     * Set serial
     *
     * @param string $serial
     */
    public function setSerial($serial)
    {
        $this->serial = $serial;
    }

    /**
     * Get serial
     *
     * @return string 
     */
    public function getSerial()
    {
        return $this->serial;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set priceLevel
     *
     * @param integer $priceLevel
     */
    public function setPriceLevel($priceLevel)
    {
        $this->priceLevel = $priceLevel;
    }

    /**
     * Get priceLevel
     *
     * @return integer 
     */
    public function getPriceLevel()
    {
        return $this->priceLevel;
    }

    /**
     * Set cover
     *
     * @param string $cover
     */
    public function setCover($cover)
    {
        $this->cover = $cover;
    }

    /**
     * Get cover
     *
     * @return string 
     */
    public function getCover()
    {
        return $this->cover;
    }

    /**
     * Set articles
     *
     * @param string $articles
     */
    public function setArticles($articles)
    {
        $this->articles = $articles;
    }

    /**
     * Get articles
     *
     * @return string 
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * Set nbArticles
     *
     * @param integer $nbArticles
     */
    public function setNbArticles($nbArticles)
    {
        $this->nbArticles = $nbArticles;
    }

    /**
     * Get nbArticles
     *
     * @return integer 
     */
    public function getNbArticles()
    {
        return $this->nbArticles;
    }

    /**
     * Set nbPages
     *
     * @param integer $nbPages
     */
    public function setNbPages($nbPages)
    {
        $this->nbPages = $nbPages;
    }

    /**
     * Get nbPages
     *
     * @return integer 
     */
    public function getNbPages()
    {
        return $this->nbPages;
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

    /**
     * Set publish
     *
     * @param boolean $publish
     */
    public function setPublish($publish)
    {
        $this->publish = $publish;
    }

    /**
     * Get publish
     *
     * @return boolean 
     */
    public function getPublish()
    {
        return $this->publish;
    }

    /**
     * Set publisher
     *
     * @param integer $publisher
     */
    public function setPublisher($publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * Get publisher
     *
     * @return integer 
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * Set nbFaved
     *
     * @param integer $nbFaved
     */
    public function setNbFaved($nbFaved)
    {
        $this->nbFaved = $nbFaved;
    }

    /**
     * Get nbFaved
     *
     * @return integer 
     */
    public function getNbFaved()
    {
        return $this->nbFaved;
    }

    /**
     * Set nbDownloaded
     *
     * @param integer $nbDownloaded
     */
    public function setNbDownloaded($nbDownloaded)
    {
        $this->nbDownloaded = $nbDownloaded;
    }

    /**
     * Get nbDownloaded
     *
     * @return integer 
     */
    public function getNbDownloaded()
    {
        return $this->nbDownloaded;
    }
}