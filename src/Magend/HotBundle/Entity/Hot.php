<?php

namespace Magend\HotBundle\Entity;

use stdClass;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

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
     * @ORM\ManyToOne(
     *     targetEntity="Magend\PageBundle\Entity\Page",
     *     inversedBy="hots"
     * )
     */
    private $page;

    /**
     * @var integer $type
     *
     * @ORM\Column(name="type", type="integer")
     */
    private $type = 0;
    
    /**
     * 
     * @var integer
     * 
     * @ORM\Column(name="x", type="integer")
     */
    private $x = 0;

    /**
     *
     * @var integer
     * 
     * @ORM\Column(name="y", type="integer")
     */
    private $y = 0;

    /**
     *
     * @var integer
     * 
     * @ORM\Column(name="w", type="integer")
     */
    public $w = 0;

    /**
     *
     * @var integer
     *
     * @ORM\Column(name="h", type="integer")
     */
    public $h = 0;
    
    /**
     * Landscape(0) or portrait(1)
     * 
     * @var integer $mode
     *
     * @ORM\Column(name="mode", type="integer")
     */
    private $mode = self::MODE_LANDSCAPE;
    
    /**
     * Serialized array of attrs
     * 
     * @var text $attrs
     *
     * @ORM\Column(name="attrs", type="text")
     */    
    private $attrs;
    
    /**
     *
     * @var HotAttrContainer
     */
    private $attrContainer;

    /**
     * serialized array of asset ids
     * 
     * @var string $assetIds
     *
     * @ORM\Column(name="asset_ids", type="string", nullable=true)
     */
    private $assetIds;
    
    /**
     * 
     * Though Hot and Asset are manytomany associated,
     * assets will be removed when its hot is removed, by
     * cascade={"all"} annotation below
     * 
     * @ORM\ManyToMany(
     *     targetEntity="Magend\AssetBundle\Entity\Asset",
     *     inversedBy="hots",
     *     indexBy="id",
     *     fetch="EXTRA_LAZY",
     *     cascade={"all"}
     * )
     * @ORM\JoinTable(name="mag_hot_asset")
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
     * Create a new Hot from HotFactory
     * 
     * @param integer $type
     */
    public function __construct($type = null)
    {
        $this->assets = new ArrayCollection();
        $this->type = $type;
        $this->attrContainer = new HotAttrContainer();
    }
    
    /**
     * __construct won't be called on load
     *
     * @ORM\PostLoad()
     */
    public function postLoad()
    {
        $attrs = $this->getAttrs();
        $this->attrContainer = new HotAttrContainer($this);
        foreach ($attrs as $name => $val) {
            $this->attrContainer->$name = $val;
        }
        
        $assets = $this->getAssets();
        foreach ($assets as $asset) {
            $this->attrContainer->addAsset($asset);
        } // @todo by order
    }
    
    /**
     * 
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        $this->createdAt = new DateTime;
    }
    
    /**
     * 
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    { 
        $this->updatedAt = new DateTime;
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

    public function getPage()
    {
        return $this->page;
    }
    
    public function setPage($page)
    {
        /*
        if (!$page->getHots()->contains($this)) {
            $page->addHot($this);
        }*/
        $this->page = $page;
    }
    
    public function getMode()
    {
        return $this->mode;
    }
    
    public function setMode($mode)
    {
        $this->mode = $mode;
    }
    
    public function getX()
    {
        return $this->x;
    }
    
    public function setX($x)
    {
        $this->x = $x;
    }
    
    public function getY()
    {
        return $this->y;
    }
    
    public function setY($y)
    {
        $this->y = $y;
    }
    
    public function getW()
    {
        return $this->w;
    }
    
    public function setW($w)
    {
        $this->w = $w;
    }
    
    public function getH()
    {
        return $this->h;
    }
    
    public function setH($h)
    {
        $this->h = $h;
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
    
    /**
     * Set assetIds
     *
     * @param array $assetIds
     */
    public function setAssetIds($assetIds)
    {
        $this->assetIds = serialize($assetIds);
    }

    /**
     * Get assetIds
     *
     * @return array 
     */
    public function getAssetIds()
    {
        return unserialize($this->assetIds);
    }
    
    /**
     * Add asset
     * 
     * @param Asset $asset
     */
    public function addAsset($asset)
    {
        $this->assets[$asset->getId()] = $asset;
        
        $assetIds = $this->getAssetIds();
        $assetIds[] = $asset->getId();
        $this->setAssetIds($assetIds);
    }
    
    public function removeAssets()
    {
        $this->assets = new ArrayCollection();
        $this->setAssetIds(null);
    }
    
    /**
     * This also affects output(xml)
     * 
     * @param bool $partial
     * @return ArrayCollection
     */
    /*
    public function getAssets($partial = true)
    {
        $assets = array();
        $assetIds = $this->getAssetIds();
        foreach ($assetIds as $assetId) {
            if (empty($this->assets[$assetId])) continue;
            
            $asset = $this->assets[$assetId]; 
            if ($partial) {
                // no id as index, or json_encoded will be different
                $assets[] = array(
                    'id'   => $asset->getId(),
                    'file' => $asset->getResource(),
                    'name' => $asset->getTag()
                );
            } else {
                $assets[$assetId] = $asset;
            }
        }
        return $assets;
    }*/
    
    public function getAssets()
    {
        return $this->assets;
    }
    
    public function setAssets($assets)
    {
        foreach ($assets as $asset) {
            $asset->addHot($this);
        }
        $this->assets = $assets;
    }
    
    public function getAttrContainer()
    {
        return $this->attrContainer;
    }
    
    public function setAttrContainer($attrContainer)
    {
        $this->attrContainer = $attrContainer;
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