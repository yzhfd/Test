<?php

namespace Magend\KeywordBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * Magend\KeywordBundle\Entity\Keyword
 *
 * @ORM\Table(name="mag_keyword")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Magend\KeywordBundle\Entity\KeywordRepository")
 * @ORM\HasLifecycleCallbacks
 * @DoctrineAssert\UniqueEntity("keyword")
 */
class Keyword
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
     * @var string $keyword
     *
     * @ORM\Column(name="keyword", type="string", length=255, unique=true)
     */
    private $keyword;

    /**
     * @var datetime $createdAt
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;
    
    /**
     * 
     * 
     * @ORM\ManyToMany(
     *     targetEntity="Magend\ArticleBundle\Entity\Article",
     *     mappedBy="keywords"
     * )
     */
    private $articles;

    /**
     * 
     * 
     * @param string $keyword
     */
    public function __construct($keyword = null)
    {
        $this->articles = new ArrayCollection();
        $this->setKeyword($keyword);
    }
    
    /**
     * 
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime;
    }
    
    public function addArticle($article)
    {
        $this->articles->add($article);
    }
    
    public function getArticles()
    {
        return $this->articles;
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
     * Set keyword
     *
     * @param string $keyword
     */
    public function setKeyword($keyword)
    {
        $keyword = trim($keyword);
        // if wanna make sure keyword is unique, check it in db
        $this->keyword = $keyword;
    }

    /**
     * Get keyword
     *
     * @return string 
     */
    public function getKeyword()
    {
        return $this->keyword;
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
    
    public function __toString()
    {
        return $this->getKeyword();
    }
}