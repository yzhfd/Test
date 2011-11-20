<?php

namespace Magend\ArticleBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;
    
    /**
     * @var string $keywords
     *
     * @ORM\Column(name="keywords", type="string", length=255, nullable=true)
     */
    private $keywords;
    
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
     * @ORM\OneToMany(targetEntity="Magend\PageBundle\Entity\Page", mappedBy="article", indexBy="id", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="mag_article_page")
     */
    private $pages;
    
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

    /**
     * Set keywords
     *
     * @param string $keywords
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
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
    
    public function getPages()
    {
        return $this->pages;
    }
}