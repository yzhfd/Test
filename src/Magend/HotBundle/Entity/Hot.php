<?php

namespace Magend\HotBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Magend\HotBundle\Entity\Hot
 *
 * @ORM\Table(name="mag_hot")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Hot
{
    
    const MODE_LANDSCAPE = 0;
    const MODE_PORTRAIT  = 1;
    
    // @todo IMAGES, VIDEO, ...
    
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var Page
     * 
     * @ORM\ManyToOne(targetEntity="Magend\PageBundle\Entity\Page", inversedBy="hots")
     */
    private $page;

    /**
     * @var integer $type
     *
     * @ORM\Column(name="type", type="integer")
     */
    private $type = 0;
    
    /**
     * Landscape(0) or portrait(1)
     * 
     * @var integer $mode
     *
     * @ORM\Column(name="mode", type="integer")
     */
    private $mode = self::MODE_LANDSCAPE;
    
    /**
     * Serialized array of position, dimension and other essential attributes
     * 
     * @var text $attrs
     *
     * @ORM\Column(name="attrs", type="text")
     */    
    private $attrs;
    
    /**
     * Serialized array of extra attributes
     * 
     * @var text $extraAttrs
     *
     * @ORM\Column(name="extra_attrs", type="text", nullable=true)
     */    
    private $extraAttrs;

    /**
     * comma separated asset ids
     * 
     * @var text $assets
     *
     * @ORM\Column(name="assets", type="text", nullable=true)
     */
    private $assets;

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
     * @ORM\PostRemove()
     */
    public function postRemove()
    {
        /*
        $assets = $this->getAssets();
        if (empty($assets)) {
            return;
        }
        foreach ($assets as $asset) {
            if (!empty($asset['file'])) {
                @unlink(__DIR__.'/../../../../web/uploads/' . $asset['file']);
            }
        }*/
    }
    
    /**
     * Set type
     *
     * @param integer $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set attrs
     *
     * @param array $attrs
     */
    public function setAttrs($attrs)
    {
        $this->attrs = is_array($attrs) ? serialize($attrs) : $attrs;
    }

    /**
     * Get attrs
     *
     * @return array 
     */
    public function getAttrs()
    {
        return unserialize($this->attrs);
    }
    
    public function getX()
    {
        $attrs = $this->getAttrs();
        return $attrs['x'];
    }
    
    public function getY()
    {
        $attrs = $this->getAttrs();
        return $attrs['y'];
    }
    
    public function getWidth()
    {
        $attrs = $this->getAttrs();
        return $attrs['width'];
    }
    
    public function getHeight()
    {
        $attrs = $this->getAttrs();
        return $attrs['height'];
    }
    
    /**
     * Set extra attrs
     *
     * @param array $extaAttrs
     */
    public function setExtraAttrs($extraAttrs)
    {
        $this->extraAttrs = is_array($extraAttrs) ? serialize($extraAttrs) : $extraAttrs;
    }

    /**
     * Get extra attrs
     *
     * @return array 
     */
    public function getExtraAttrs()
    {
        return unserialize($this->extraAttrs);
    }
    
    /**
     * Set assets
     *
     * @param array|string $assets
     */
    public function setAssets($assets)
    {
        if (is_array($assets)) {
            $assets = implode(',', $assets);
        }
        $this->assets = $assets;
    }

    /**
     * Get assets
     *
     * @return array 
     */
    public function getAssets()
    {
        return $this->assets ? explode(',', $this->assets) : array();
    }
    
    /**
     * Add asset
     * 
     * @param Asset $asset
     */
    public function addAsset($asset)
    {
        $assets = $this->getAssets();
        $assets[] = $asset->getId();
        $this->setAssets($assets);
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
    
    public function getPage()
    {
        return $this->page;
    }
    
    public function setPage($page)
    {
        $this->page = $page;
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
    
    public function getMode()
    {
        return $this->mode;
    }
    
    public function setMode($mode)
    {
        $this->mode = $mode;
    }
}