<?php

namespace Magend\PageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Magend\PageBundle\Entity\Page
 *
 * @ORM\Table(name="mag_page")
 * @ORM\Entity(repositoryClass="Magend\PageBundle\Entity\PageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Page
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
     * @ORM\ManyToOne(targetEntity="Magend\ArticleBundle\Entity\Article", inversedBy="pages")
     */
    private $article;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}