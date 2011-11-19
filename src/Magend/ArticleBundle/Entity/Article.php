<?php

namespace Magend\ArticleBundle\Entity;

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
     * The issue this article belongs to
     * 
     * @ORM\ManyToOne(targetEntity="Magend\IssueBundle\Entity\Issue", inversedBy="articles")
     */
    private $issue;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;
    
    /**
     * @var string $keywords
     *
     * @ORM\Column(name="keywords", type="string", length=255)
     */
    private $keywords;
    
    /**
     * serialized $pagesArray
     * 
     * @var string $pages
     *
     * @ORM\Column(name="pages", type="text")
     */
    private $pages;
    
    /**
     * array of page ids
     * 
     * @var array
     */
    private $pagesArray;
    
    /**
     * @var integer $nbShared
     *
     * @ORM\Column(name="nb_shared", type="integer")
     */
    private $nbShared;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
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
    
    public function getPages()
    {
        return $this->pagesArray;
    }
    
    public function setPages(Array $pages)
    {
        $this->pagesArray = $pages;
    }
}