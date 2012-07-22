<?php

namespace Magend\AssetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * We use a string to group assets
     *
     * @var string $groupedTo
     *
     * @ORM\Column(name="grouped_to", type="string", length=255, nullable=true)
     */
    private $groupedTo;
    
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
    }
    
    /**
     * 
     * @ORM\PostRemove()
     */
    public function postRemove()
    {
        if ($this->getResource()) {
            @unlink(__DIR__.'/../../../../web/uploads/' . $this->getResource());
        }
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