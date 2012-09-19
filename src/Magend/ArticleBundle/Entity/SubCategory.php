<?php

namespace Magend\ArticleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Magend\ArticleBundle\Entity\SubCategory
 *
 * @ORM\Table(name="mag_article_subcategory")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class SubCategory
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
     * @var Category
     * 
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="subCategories")
     * @ORM\JoinColumn(
     *     onDelete="CASCADE"
     * )
     */
    private $category;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string $icon
     *
     * @ORM\Column(name="icon", type="string", length=255, nullable=true)
     */
    private $icon;
    
    /**
     *
     * @var File
     */
    public $iconFile;


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
    public function preFlush()
    {
        if ($this->iconFile) {
            $imgName = uniqid('cat_') . '.' . $this->iconFile->guessExtension();
            $this->iconFile->move(__DIR__.'/../../../../web/uploads/', $imgName);
    
            if ($this->getIcon()) {
                @unlink(__DIR__.'/../../../../web/uploads/' . $this->getIcon());
            }
    
            $this->setIcon($imgName);
        }
    }
    
    /**
     *
     * @ORM\PostRemove()
     */
    public function postRemove()
    {
        if ($this->getIcon()) {
            @unlink(__DIR__.'/../../../../web/uploads/' . $this->getIcon());
        }
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
     * Set icon
     *
     * @param string $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * Get icon
     *
     * @return string 
     */
    public function getIcon()
    {
        return $this->icon;
    }
    
    public function getCategory()
    {
        return $this->category;
    }
    
    public function setCategory($category)
    {
        $this->category = $category;
    }
}