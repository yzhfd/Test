<?php

namespace Magend\AssetBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Magend\AssetBundle\Entity\Asset
 *
 * @ORM\Table(name="mag_asset")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Asset
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
     * Video, audio or image, etc
     * 
     * @var smallint $type
     *
     * @ORM\Column(name="type", type="smallint")
     */
    private $type = 0;
    
    /**
     * Sequence number of the asset
     * 
     * @var integer $seq
     * @ORM\Column(name="seq", type="integer")
     */
    private $seq = 0;

    /**
     * We use a string to group assets
     *
     * @var string $groupedTo
     *
     * @ORM\Column(name="grouped_to", type="string", length=255, nullable=true)
     */
    private $groupedTo;
    
    /**
     *
     * @ORM\ManyToOne(
     *     targetEntity="Magend\HotBundle\Entity\Hot",
     *     inversedBy="assets",
     *     fetch="EXTRA_LAZY"
     * )
     */
    private $hot;
    
    /**
     * Currently name of the file
     * 
     * @var string $tag
     *
     * @ORM\Column(name="tag", type="string", length=255, nullable=true)
     */
    private $tag;

    /**
     * @var string $resource
     *
     * @ORM\Column(name="resource", type="string", length=255)
     */
    private $resource;
    
    /**
     * 
     * @var UploadedFile
     */
    public $resourceFile;

    /**
     * @var text $info
     *
     * @ORM\Column(name="info", type="text", nullable=true)
     */
    private $info;

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
    
    public function __construct()
    {
        $this->hots = new ArrayCollection();
    }
    
    /**
     * 
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function prePersist()
    {
        $now = new DateTime;
        if (null === $this->createdAt) {
            $this->createdAt = $now;
        }
        
        if ($this->resourceFile) {
            $assetName = uniqid('asset_') . '.' . $this->resourceFile->guessExtension();
            // @todo maybe not guessed out
            $this->resourceFile->move(__DIR__.'/../../../../web/uploads/', $assetName);
            
            if ($this->getResource()) {
                @unlink(__DIR__.'/../../../../web/uploads/' . $this->getResource());
            }
            
            $this->setResource($assetName);
        }
        
        // echo $this->getTag();exit;
    }
    
    /**
     * 
     * @ORM\PostRemove()
     */
    public function postRemove()
    {
        // no delete because of clone
        /*
        if ($this->getResource()) {
            @unlink(__DIR__.'/../../../../web/uploads/' . $this->getResource());
        }*/
    }

    /**
     * Set type
     *
     * @param smallint $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return smallint 
     */
    public function getType()
    {
        return $this->type;
    }
    
    public function getGroupedTo()
    {
        return $this->groupedTo;
    }
    
    public function setGroupedTo($groupedTo)
    {
        $this->groupedTo = $groupedTo;
    }

    /**
     * Set resource
     *
     * @param string $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * Get resource
     *
     * @return string 
     */
    public function getResource()
    {
        return $this->resource;
    }
    
    /**
     * Set tag
     *
     * @param string $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    /**
     * Get tag
     *
     * @return string 
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Set info
     *
     * @param text $info
     */
    public function setInfo($info)
    {
        $this->info = $info;
    }

    /**
     * Get info
     *
     * @return text 
     */
    public function getInfo()
    {
        return $this->info;
    }
    
    public function getHot()
    {
        return $this->hot;
    }
    
    public function setHot($hot)
    {
        $this->hot = $hot;
    }
    
    public function getSeq()
    {
        return $this->seq;
    }
    
    public function setSeq($seq)
    {
        $this->seq = $seq;
    }
    
    public function isImage()
    {
        $nameArr = explode('.', $this->getResource());
        $ext = array_pop($nameArr);
        return in_array($ext, array('jpg', 'jpeg', 'png'));
    }
    
    public function isAudio()
    {
        $nameArr = explode('.', $this->getResource());
        $ext = array_pop($nameArr);
        return in_array($ext, array('mp3', 'wav'));
    }
    
    public function isVideo()
    {
        $nameArr = explode('.', $this->getResource());
        $ext = array_pop($nameArr);
        return in_array($ext, array('mp4', 'avi'));
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