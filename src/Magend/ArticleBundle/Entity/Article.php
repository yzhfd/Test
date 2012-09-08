<?php

namespace Magend\ArticleBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Magend\KeywordBundle\Entity\Keyword;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
USE Magend\PageBundle\Entity\Page;

/**
 * Magend\ArticleBundle\Entity\Article
 *
 * @ORM\Table(name="mag_article")
 * @ORM\Entity(repositoryClass="Magend\ArticleBundle\Entity\ArticleRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Article
{
    const TYPE_ADS    = 0;      
    const TYPE_COVER  = 1;
    const TYPE_INDEX  = 2;
    const TYPE_RIGHT  = 3;
    const TYPE_PROJ   = 4;
    const TYPE_INST   = 5;
    const TYPE_PEOPLE = 6;
    const TYPE_ARCTH  = 7;
    const TYPE_ELSE   = 8;
    
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
     * @ORM\OneToMany(
     *     targetEntity="Fav",
     *     mappedBy="article"
     * )
     */
    private $favs;

    /**
     * Now(and normally) article only belongs to just one issue
     * 
     * @var ArrayCollection
     * 
     * @ORM\ManyToMany(targetEntity="Magend\IssueBundle\Entity\Issue", mappedBy="articles")
     */
    private $issues;
    
    /**
     * @var smallint $type
     *
     * @ORM\Column(name="type", type="smallint")
     */
    private $type;
    
    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;
    
    /**
     * @var string $enTitle
     *
     * @ORM\Column(name="en_title", type="string", length=255, nullable=true)
     */
    private $enTitle;    
    
    /**
     * Name of audio file on the server
     * 
     * @var string $audio
     * 
     * @ORM\Column(name="audio", type="string", length=255, nullable=true)
     */
    private $audio;
    
    /** 
     * Original audio file name
     * 
     * @var string $audioName
     * 
     * @ORM\Column(name="audio_name", type="string", length=255, nullable=true)
     */
    private $audioName;    
    
    /**
     * @var File
     * 
     */
    public $audioFile;
    
    /**
     * 
     * @ORM\ManyToMany(
     *     targetEntity="Magend\KeywordBundle\Entity\Keyword",
     *     inversedBy="articles",
     *     cascade={"persist"},
     *     fetch="EXTRA_LAZY"
     * )
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
     * 
     * @var ArrayCollection
     * 
     * @ORM\OneToMany(
     *     targetEntity="Magend\PageBundle\Entity\Page",
     *     mappedBy="article",
     *     indexBy="id",
     *     cascade={"persist", "remove"},
     *     fetch="EXTRA_LAZY"
     * )
     * @ORM\OrderBy({"seq" = "ASC"})
     */
    private $pages;
    
    /**
     * 
     * @var interger
     * 
     * @ORM\Column(name="nb_pages", type="smallint")
     */
    private $nbPages = 0;
    
    /**
     * Article's comments
     * 
     * @var ArrayCollection
     * 
     * @ORM\OneToMany(
     *     targetEntity="Comment",
     *     mappedBy="article",
     *     indexBy="id",
     *     cascade={"persist", "remove"},
     *     fetch="EXTRA_LAZY"
     * )
     */
    private $comments;
    
    /**
     * Institutes
     * Only articles of type TYPE_PROJ have
     * 
     * @var ArrayCollection
     * 
     * @ORM\ManyToMany(
     *     targetEntity="Magend\InstituteBundle\Entity\Institute",
     *     fetch="EXTRA_LAZY"
     * )
     * @ORM\JoinTable(name="mag_article_institute")
     */
    private $institutes;
    
    /**
     * @var Project
     * 
     * @ORM\ManyToOne(targetEntity="Magend\ProjectBundle\Entity\Project", inversedBy="pages")
     */
    private $project;
    
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

    /**
     * Project's address, complement witt lat and lng
     * 
     * @var string $prjectAddr
     * @ORM\Column(name="project_addr", type="string", length=255, nullable=true)
     */
    private $projectAddr;
    
    // @todo altitude, not supported by google map directly
    
    /**
     * @var integer $nbShared
     *
     * @ORM\Column(name="nb_shared", type="integer")
     */
    private $nbShared = 0;
    
    /**
     * 
     * @var integer
     * 
     * @ORM\Column(name="nb_favs", type="integer")
     */
    private $nbFavs = 0;

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
     * @var Magzine
     * 
     * @ORM\ManyToOne(targetEntity="Magend\MagzineBundle\Entity\Magzine", inversedBy="copyrightArticles")
     */
    private $copyrightMagzine;
    
    // @todo postRemove
    
    public function __construct()
    {
        $this->favs = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->pages = new ArrayCollection();
        $this->architects = new ArrayCollection();
        $this->issues = new ArrayCollection();
        $this->institutes = new ArrayCollection();
    }
    
    public function clonePages()
    {
        $pages = $this->getPages();
        $this->pages = new ArrayCollection();
        foreach ($pages as $page) {
            $clonePage = clone $page;
            $clonePage->cloneHots();
            $this->pages->add($clonePage);
            $clonePage->setArticle($this);
        }
    }
    
    public static function getTypeList()
    {
        $types = array();
        for ($i=0; $i<4; ++$i) {
            $types[] = "article.$i";
        }
        return $types;
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
    
    public function setId($id)
    {
        $this->id = $id;
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
        
        if ($this->audioFile) {
            $fileName = uniqid('audio_') . '.' . $this->audioFile->guessExtension();
            $this->audioFile->move(__DIR__.'/../../../../web/uploads/', $fileName);
            
            if ($this->getAudio()) {
                @unlink(__DIR__.'/../../../../web/uploads/' . $this->getAudio());
            }
        
            $this->setAudio($fileName);
        }
    }
    
    /**
     * 
     * @ORM\PostRemove()
     */
    public function postRemove()
    {
        if ($this->getAudio()) {
            @unlink(__DIR__.'/../../../../web/uploads/' . $this->getAudio());
        }
    }
    
    /**
     * 
     * @ORM\PreRemove()
     */
    public function preRemove()
    {
        $issues = $this->getIssues();
        if (empty($issues)) {
            return;
        }
        foreach ($issues as $issue) {
            $issue->removeArticle($this);
        }
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
        if (!empty($keywords)) {
            foreach ($keywords as $keyword) {
                $kws[] = $keyword->getKeyword();
            }
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
     * Set nbFavs
     *
     * @param integer $nbFavs
     */
    public function setNbFavs($nbFavs)
    {
        $this->nbFavs = $nbFavs;
    }
    
    public function incNbFavs()
    {
        ++$this->nbFavs;
    }
    
    public function decNbFavs()
    {
        --$this->nbFavs;
    }
    
    /**
     * Get nbFavs
     *
     * @return integer
     */
    public function getNbFavs()
    {
        return $this->nbFavs;
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
     * Set enTitle
     *
     * @param string $enTitle
     */
    public function setEnTitle($enTitle)
    {
        $this->enTitle = $enTitle;
    }

    /**
     * Get enTitle
     *
     * @return string 
     */
    public function getEnTitle()
    {
        return $this->enTitle;
    }

    /**
     * Set audio
     *
     * @param string $audio
     */
    public function setAudio($audio)
    {
        $this->audio = $audio;
    }

    /**
     * Get audio name
     *
     * @return string 
     */
    public function getAudioName()
    {
        return $this->audioName;
    }
    
    /**
     * Set audio name
     *
     * @param string $audioName
     */
    public function setAudioName($audioName)
    {
        $this->audioName = $audioName;
    }

    /**
     * Get audio
     *
     * @return string 
     */
    public function getAudio()
    {
        return $this->audio;
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
    
    public function getPageCollection()
    {
        return $this->pages;
    }
    
    /**
     * 
     * @todo which type
     * @return array
     */ 
    public function getPages()
    {
        return $pages;
    }
    
    public function setPages($pages)
    {
        if (is_array($pages)) {
            $pages = new ArrayCollection($pages);
        }
        $this->pages = $pages;
    }
    
    /**
     * 
     * @return integer
     */
    public function getNbPages()
    {
        return $this->nbPages;
    }
    
    public function setNbPages($nbPages)
    {
        $this->nbPages = $nbPages;
    }
    
    public function getThumbnail()
    {
        $pages = $this->getPages();        
        if (empty($pages)) {
            return null;
        }
        
        $firstPage = $pages->first();
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
     * Get project address
     * 
     * @return string
     */
    public function getProjectAddr()
    {
        return $this->projectAddr;
    }
    
    /**
     * Set project address
     * 
     * @param string $projectAddr
     */
    public function setProjectAddr($projectAddr)
    {
        $this->projectAddr = $projectAddr;
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
     * Add page
     *
     * @param Page $page
     */
    public function addPage(Page $page)
    {
        $this->pages[] = $page;
    }

    /**
     * Set type
     *
     * @param smallint $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return smallint 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add institute
     *
     * @param Magend\InstituteBundle\Entity\Institute $institute
     */
    public function addInstitute(\Magend\InstituteBundle\Entity\Institute $institute)
    {
        $this->institutes[] = $institute;
    }

    /**
     * Get institutes
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getInstitutes()
    {
        return $this->institutes;
    }

    /**
     * Set project
     *
     * @param Magend\ProjectBundle\Entity\Project $project
     */
    public function setProject(\Magend\ProjectBundle\Entity\Project $project)
    {
        $this->project = $project;
    }

    /**
     * Get project
     *
     * @return Magend\ProjectBundle\Entity\Project 
     */
    public function getProject()
    {
        return $this->project;
    }
    
    public function __toString()
    {
        return $this->getTitle();
    }
    
    /**
     * 
     * @return bool
     */
    public function isCopyright()
    {
        return $this->getCopyrightMagzine() != null;
    }

    /**
     * Set copyrightMagzine
     *
     * @param Magzine $copyrightMagzine
     */
    public function setCopyrightMagzine($mag)
    {
        $this->copyrightMagzine = $mag;
    }

    /**
     * Get copyrightMagzine
     *
     * @return string 
     */
    public function getCopyrightMagzine()
    {
        return $this->copyrightMagzine;
    }
    
    /**
     * 
     * @return ArrayCollection
     */
    public function getComments()
    {
        return $this->comments;
    }
    
    public function addComment(Comment $cmt)
    {
        $this->comments[] = $cmt;
    }
}