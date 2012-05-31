<?php

namespace Magend\ArticleBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Magend\ArticleBundle\Entity\Fav
 *
 * @ORM\Table(name="mag_fav", uniqueConstraints={@ORM\UniqueConstraint(name="fav", columns={"article_id", "user_id"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Fav
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
     * @ORM\ManyToOne(
     *     targetEntity="Article",
     *     inversedBy="favs"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $article;
    
    /**
     * @var User
     * 
     * @ORM\ManyToOne(
     *     targetEntity="Magend\UserBundle\Entity\User",
     *     inversedBy="favs"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $user;

    /**
     * @var datetime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;


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
        $this->createdAt = new DateTime;
        $this->article->incNbFavs();
    }
    
    /**
     * 
     * @ORM\PreRemove()
     */
    public function preRemove()
    {
        $this->article->decNbFavs();
    }
    
    public function getArticle()
    {
        return $this->article;
    }
    
    public function setArticle($article)
    {
        $this->article = $article;
    }
    
    public function getUser()
    {
        return $this->user;
    }
    
    public function setUser($user)
    {
        $this->user = $user;
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
}