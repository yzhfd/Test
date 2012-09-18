<?php

namespace Magend\ArticleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Magend\ArticleBundle\Entity\Category
 *
 * @ORM\Table(name="mag_article_category")
 * @ORM\Entity
 */
class Category
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
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;
    
    /**
     * 
     * @var SubCategory
     * 
     * @ORM\OneToMany(
     *     targetEntity="SubCategory",
     *     mappedBy="subcategories"
     * )
     */
    private $subcategories;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function __construct()
    {
        $this->subcategories = new ArrayCollection();
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
    
    public function getSubCategories()
    {
        return $this->subcategories;
    }
    
    public function addSubCategory($subcategory)
    {
        $this->subcategories[] = $subcategory;
    }
}