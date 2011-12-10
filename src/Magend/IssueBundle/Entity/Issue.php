<?php

namespace Magend\IssueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Magend\ArticleBundle\Entity\Article;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

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
     * @Assert\NotBlank
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
     * @ORM\Column(name="cover", type="string", length=255, nullable=true)
     */
    private $cover;

    /**
     * Comma separated article ids
     * used to maitain the order of articles in this issue
     * 
     * @var string $articleIds
     *
     * @ORM\Column(name="article_ids", type="text", nullable=true)
     */
    private $articleIds;
    
    /**
     * Normally one article can only belong to one issue
     * But we don't need limit this
     * 
     * cascade will not happen unless you define it
     * cascade={"persist", "remove"}
     * 
     * @ORM\ManyToMany(targetEntity="Magend\ArticleBundle\Entity\Article", inversedBy="issues", indexBy="id")
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
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var boolean $publish
     *
     * @ORM\Column(name="publish", type="boolean")
     */
    private $publish = false;

    /**
     * @var integer $publisher
     *
     * @ORM\Column(name="publisher", type="integer")
     */
    private $publisher = 0;

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
     * 
     * @var File
     */
    public $coverImage;


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
    public function prePersist()
    {
        $now = new \DateTime;
        if (null === $this->createdAt) {
            $this->createdAt = $now;
        } else {
            $this->updatedAt = $now;
        }
        
        if ($this->coverImage) {
            $imgName = uniqid('issue_') . '.' . $this->coverImage->guessExtension();
            $this->coverImage->move(__DIR__.'/../../../../web/uploads/', $imgName);
            
            if ($this->getCover()) {
                @unlink(__DIR__.'/../../../../web/uploads/' . $this->getCover());
            }
            
            $this->setCover($imgName);
        }
    }
    
    /**
     * 
     * @ORM\PostRemove()
     */
    public function postRemove()
    {
        if ($this->getCover()) {
            @unlink(__DIR__.'/../../../../web/uploads/' . $this->getCover());
        }
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
     * Set articleIds
     *
     * @param array|string $articleIds
     */
    public function setArticleIds($articleIds)
    {
        // if consistency is important, then fetch articles and check
        if (is_array($articleIds)) {
            $articleIds = implode(',', $articleIds);
        }
        $this->articleIds = $articleIds;
    }
    
    /**
     * The usual way is create article first, and then
     * attach it to one issue(and reorder)
     * 
     * @param Article $article
     */
    public function addArticle(Article $article)
    {
        if ($this->articles->contains($article)) {
            return;
        }
        
        /*$articleId = $article->getId();
        if ($articleId === null) {
            throw new \ Exception('Please persist article first');
        }*/
        
        $this->articles->add($article);
        
        // update idArray separately
        // $idArray = $this->getArticleIds();
        // $idArray[] = $articleId;
        // $this->setArticleIds($idArray);
    }

    /**
     * Get articleIds
     *
     * @return string 
     */
    public function getArticleIds()
    {
        return $this->articleIds;
    }
    
    /**
     * 
     * @param bool $withOrder the order specified in $articleIds
     */
    public function getArticles($withOrder = true)
    {
        $articles = array();
        $articleIds = $this->getArticleIds();
        if (!empty($articleIds)) {
            $articleIds = explode(',', $articleIds);
            foreach ($articleIds as $articleId) {
                $articles[$articleId] = $this->articles[$articleId];
            }
        }
        
        return !empty($articles) ? $articles : $this->articles;
    }

    /**
     * Get nbArticles
     *
     * @return integer 
     */
    public function getNbArticles()
    {
        return count($this->getArticleIds());
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