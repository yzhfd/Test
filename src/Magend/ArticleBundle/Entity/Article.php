<?php

namespace Magend\ArticleBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Magend\KeywordBundle\Entity\Keyword;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * Magend\ArticleBundle\Entity\Article
 *
 * @ORM\Table(name="mag_article")
 * @ORM\Entity(repositoryClass="Magend\ArticleBundle\Entity\ArticleRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Article
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
     * Now(and normally) article only belongs to just one issue
     * 
     * @var ArrayCollection
     * 
     * @ORM\ManyToMany(targetEntity="Magend\IssueBundle\Entity\Issue", mappedBy="articles")
     */
    private $issues;
    
    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;
    
    /**
     * Original file name prepended with random-generated unique string
     * 
     * @var string $audioFile
     * 
     * @ORM\Column(name="audio_file", type="string", length=255, nullable=true)
     */
    private $audioFile;
    
    /**
     * 
     * @ORM\ManyToMany(targetEntity="Magend\KeywordBundle\Entity\Keyword", inversedBy="articles", cascade={"persist"})
     * @ORM\JoinTable(name="mag_article_keyword")
     */
    private $keywords;
    
    /**
     * Comma separated keywords chunk
     * 
     * @var string
     */
    public $keywordsText;
    
    /**
     * 
     * @ORM\ManyToMany(targetEntity="Magend\ArchitectBundle\Entity\Architect", inversedBy="articles", cascade={"persist"})
     * @ORM\JoinTable(name="mag_article_architect")
     */
    private $architects;
    
    /**
     * Comma separated architects chunk
     * 
     * @var string
     */
    public $architectsText;
    
    /**
     * Comma separated text of page ids
     * 
     * @var string $pages
     *
     * @ORM\Column(name="page_ids", type="text", nullable=true)
     */
    private $pageIds;
    
    /**
     * @var ArrayCollection
     * 
     * 
     * @ORM\OneToMany(
     *     targetEntity="Magend\PageBundle\Entity\Page",
     *     mappedBy="article",
     *     indexBy="id",
     *     cascade={"persist", "remove"},
     *     fetch="EXTRA_LAZY"
     * )
     */
    private $pages;
    
    /**
     * Latitude
     * 
     * @var float $lat
     * @ORM\Column(name="lat", type="float")
     */
    private $lat = 0.0;
    
    /**
     * Longitude
     * 
     * @var float $lng
     * @ORM\Column(name="lng", type="float")
     */
    private $lng = 0.0;
    
    // @todo altitude, not supported by google map directly
    
    /**
     * @var integer $nbShared
     *
     * @ORM\Column(name="nb_shared", type="integer")
     */
    private $nbShared = 0;

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
    
    // @todo postRemove
    
    public function __construct()
    {
        $this->pages = new ArrayCollection();
        $this->keywords = new ArrayCollection();
        $this->architects = new ArrayCollection();
        $this->issues = new ArrayCollection();
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
    }
    
    public function setPageIds($pageIds)
    {
        if (is_array($pageIds)) {
            $pageIds = implode(',', $pageIds);
        }
        $this->pageIds = $pageIds;
    }
    
    public function getPageIds()
    {
        return $this->pageIds ? explode(',', trim($this->pageIds, ',')) : array();
    }
    
    /**
     * 
     * @param string|Keyword $keyword
     */
    public function addKeyword($keyword)
    {
        $keyword = trim($keyword);
        if (is_string($keyword)) {
            // @todo find it first
            $keyword = new Keyword($keyword);
        }
        $this->keywords->add($keyword);
    }

    /**
     * Get keywords
     *
     * @return string 
     */
    public function getKeywords()
    {
        return $this->keywords;
    }
    
    public function setKeywords($keywords)
    {
        $this->keywords = new ArrayCollection($keywords);
    }
    
    public function getKeywordsText()
    {
        if (!empty($this->keywordsText)) {
            return $this->keywordsText;
        }
        
        $keywords = $this->getKeywords();
        $kws = array();
        foreach ($keywords as $keyword) {
            $kws[] = $keyword->getKeyword();
        }
        return implode(',', $kws);
    }
    
    public function getArchitectsText()
    {
        if (!empty($this->architectsText)) {
            return $this->architectsText;
        }
        
        $arts = $this->getArchitects();
        $artNames = array();
        foreach ($arts as $art) {
            $artNames[] = $art->getName();
        }
        
        return implode(',', $artNames);
    }
    
    public function addArchitect($architect)
    {
        $this->architects->add($architect);
    }
    
    public function getArchitects()
    {
        return $this->architects;
    }
    
    public function setArchitects($architects)
    {
        if (is_string($architects)) {
            $architects = explode(',', $architects);
            foreach ($architects as $architect) {
                $this->addArchitect($architect);
            }
            return;
        }
        $this->architects = new ArrayCollection($architects);
    }

    /**
     * Set nbShared
     *
     * @param integer $nbShared
     */
    public function setNbShared($nbShared)
    {
        $this->nbShared = $nbShared;
    }

    /**
     * Get nbShared
     *
     * @return integer 
     */
    public function getNbShared()
    {
        return $this->nbShared;
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
     * Set audioFile
     *
     * @param string $audioFile
     */
    public function setAudioFile($audioFile)
    {
        $this->audioFile = $audioFile;
    }

    /**
     * Get audioFile
     *
     * @return string 
     */
    public function getAudioFile()
    {
        return $this->audioFile;
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
    
    public function getIssue()
    {
        return $this->issues->first();
    }
    
    public function setIssue($issue)
    {
        $this->issues = new ArrayCollection(array($issue));
    }
    
    // return pages ordered by pageIds
    public function getPages()
    {
        $pages = array();
        $pageIds = $this->getPageIds();
        foreach ($pageIds as $pageId) {
            if (!empty($this->pages[$pageId])) {
                $pages[$pageId] = $this->pages[$pageId];
            }
        }
        return $pages;
    }
    
    public function setPages($pages)
    {
        if (is_array($pages)) {
            $pages = new ArrayCollection($pages);
        }
        $this->pages = $pages;
    }
    
    public function getNbPages()
    {
        $pageIds = $this->getPageIds();
        return count($pageIds);
    }
    
    public function getThumbnail()
    {
        $pages = $this->getPages();        
        $pageIds = $this->getPageIds();
        if (empty($pages) || empty($pageIds)) {
            return null;
        }
        
        $firstPage = $pages[$pageIds[0]];
        if (!$firstPage) {
            return null;
        }
        return $firstPage->getLandscapeImg();
    }

    /**
     * Set lat
     *
     * @param float $lat
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
    }

    /**
     * Get lat
     *
     * @return float 
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lng
     *
     * @param float $lng
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
    }

    /**
     * Get lng
     *
     * @return float 
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Add issues
     *
     * @param Magend\IssueBundle\Entity\Issue $issues
     */
    public function addIssue(\Magend\IssueBundle\Entity\Issue $issue)
    {
        $this->issues[] = $issue;
    }

    /**
     * Get issues
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getIssues()
    {
        return $this->issues;
    }

    /**
     * Add pages
     *
     * @param Magend\PageBundle\Entity\Page $pages
     */
    public function addPage(\Magend\PageBundle\Entity\Page $pages)
    {
        $this->pages[] = $pages;
    }
}