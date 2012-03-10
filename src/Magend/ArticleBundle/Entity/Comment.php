<?php

namespace Magend\ArticleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Magend\UserBundle\Entity\User;
use Magend\ArticleBundle\Entity\Article;

/**
 * Magend\CommentBundle\Entity\Comment
 *
 * @ORM\Table(name="mag_comment")
 * @ORM\Entity
 */
class Comment
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
     * 
     * @var Article
     * 
     * @ORM\ManyToOne(targetEntity="Article")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $article;
    
    /**
     * User who left the comment
     * 
     * @var User
     * @todo support guest
     * 
     * @ORM\ManyToOne(targetEntity="Magend\UserBundle\Entity\User")
     * @ORM\JoinColumn(
     *     name="user_id",
     *     referencedColumnName="id",
     *     onDelete="CASCADE"
     * )
     */
    private $user;

    /**
     * @var text $body
     *
     * @ORM\Column(name="body", type="text")
     */
    private $body;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        $this->createdAt = new DateTime();
    }
    
    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        $this->updatedAt = new DateTime();
    }

    /**
     * Set body
     *
     * @param text $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * Get body
     *
     * @return text 
     */
    public function getBody()
    {
        return $this->body;
    }
    
    /**
     * Set article
     *
     * @param Article $article
     */
    public function setArticle(Article $article)
    {
        $this->article = $article;
    }

    /**
     * Get user
     *
     * @return Article
     */
    public function getArticle()
    {
        return $this->article;
    }
    
    /**
     * Set user
     *
     * @param Magend\UserBundle\Entity\User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return Magend\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
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
}