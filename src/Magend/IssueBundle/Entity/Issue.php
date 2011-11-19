<?php

namespace Magend\IssueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Magend\ArticleBundle\Entity\Article;

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
     * Serialized $articleIdsArray
     * used to maitain the order of articles in this issue
     * 
     * @var string $articleIds
     *
     * @ORM\Column(name="article_ids", type="text")
     */
    private $articleIds;
    
    /**
     * array of article ids
     * 
     * @var array
     */
    private $articleIdsArray;
    
    /**
     * Now it's OneToMany, it can be changed to ManyToMany
     * if one article can be in more than one issues.
     * 
     * cascade will not happen unless you define it
     * cascade={"persist", "remove"}
     * 
     * @ORM\ManyToMany(targetEntity="Magend\ArticleBundle\Entity\Article")
     * @ORM\JoinTable(name="mag_issue_article")
     */
    private $articles;

    /**
     * @todo use a field or compute from articles
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


    public function __construct()
    {
        $this->articleIdsArray = array();
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
    public function prePersist()
    {
        $now = new \DateTime;
        if (null === $this->createdAt) {
            $this->createdAt = $now;
        }
        $this->updatedAt = $now;
        
        $sArticleIds = serialize($this->articleIdsArray);
    }
    
    /**
     * 
     * @ORM\PostRemove()
     */
    public function postRemove()
    {
        // @todo delete cover
        // @todo delete issue_id in articles
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
     * Set articles(array)
     *
     * @param array $articles
     */
    public function setArticleIds(Array $articles)
    {
        $this->articleIdsArray = $articles;
    }
    
    /**
     * 
     * @param int|Article $article
     */
    public function addArticle(Article $article)
    {
        if (is_object($article)) {
            $articleId = $article->getId();
        } else {
            $articleId = $article;
        }
        $this->articleIdsArray[] = $articleId;
    }

    /**
     * Get articles(array)
     *
     * @return string 
     */
    public function getArticleIds()
    {
        return $this->articleIdsArray;
    }
    
    public function getArticles()
    {
        return $this->articles;        
    }

    /**
     * Get nbArticles
     *
     * @return integer 
     */
    public function getNbArticles()
    {
        return count($this->articlesArray);
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