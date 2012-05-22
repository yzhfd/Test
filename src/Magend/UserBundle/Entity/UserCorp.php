<?php

namespace Magend\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Magend\UserBundle\Entity\UserCorp
 *
 * @ORM\Table(name="mag_user_corp")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class UserCorp
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
     * @ORM\OneToOne(targetEntity="User", mappedBy="corp")
     * @var User
     */
    private $user;
    
    /**
     * Phone number
     * 
     * @var string
     * @ORM\Column(name="phone", type="string", length=64, nullable=true)
     */
    private $phone;
    
    /**
     * Corporation name
     * 
     * @var string
     * @ORM\Column(name="name", type="string", length=128)
     */
    private $name;
    
    /**
     * Corporation legal person's name
     * 
     * @var string
     * @ORM\Column(name="legal_person", type="string", length=32)
     */
    private $legalPerson;
    
    /**
     * Org Code
     * 
     * @var string
     * @ORM\Column(name="org_code", type="string", length=255, nullable=true)
     */
    private $orgCode;
    
    /**
     * 
     * @var UploadedFile
     * 
     * @Assert\Image(
     *     mimeTypesMessage = "不是有效图片",
     *     maxSize = "2000000",
     *     maxSizeMessage = "图片大小需小于2M"
     * ) 
     */
    public $orgCodeFile;
    
    /**
     * Business License
     * 
     * @var string
     * @ORM\Column(name="license", type="string", length=255, nullable=true)
     */
    private $license;
    
    /**
     * 
     * @var UploadedFile
     * 
     * @Assert\Image(
     *     mimeTypesMessage = "不是有效图片",
     *     maxSize = "2000000",
     *     maxSizeMessage = "图片大小需小于2M"
     * ) 
     */
    public $licenseFile;
    
    /**
     * Identity of Corporation contact person
     * 
     * @var string
     * @ORM\Column(name="contact_id", type="string", length=255)
     */
    private $contactId;
    
    /**
     * Pledge not to do anything evil
     * 
     * @var string
     * @ORM\Column(name="pledge", type="string", length=255, nullable=true)
     */
    private $pledge;
    
    /**
     * 
     * @var UploadedFile
     * 
     * @Assert\Image(
     *     mimeTypesMessage = "不是有效图片",
     *     maxSize = "2000000",
     *     maxSizeMessage = "图片大小需小于2M"
     * ) 
     */
    public $pledgeFile;

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
        if ($this->orgCodeFile) {
            $fileName = uniqid('org_') . '.' . $this->orgCodeFile->guessExtension();
            $this->orgCodeFile->move(__DIR__.'/../../../../web/uploads/corp/', $fileName);
            
            if ($this->getOrgCode()) {
                @unlink(__DIR__.'/../../../../web/uploads/corp/' . $this->getOrgCode());
            }
            
            $this->setOrgCode($fileName);
        }
        
        if ($this->licenseFile) {
            $fileName = uniqid('lcs_') . '.' . $this->licenseFile->guessExtension();
            $this->licenseFile->move(__DIR__.'/../../../../web/uploads/corp/', $fileName);
            
            if ($this->getLicense()) {
                @unlink(__DIR__.'/../../../../web/uploads/corp/' . $this->getLicense());
            }
            
            $this->setLicense($fileName);
        }

        if ($this->pledgeFile) {
            $fileName = uniqid('plg_') . '.' . $this->pledgeFile->guessExtension();
            $this->pledgeFile->move(__DIR__.'/../../../../web/uploads/corp/', $fileName);
            
            if ($this->getPledge()) {
                @unlink(__DIR__.'/../../../../web/uploads/corp/' . $this->getPledge());
            }
            
            $this->setPledge($fileName);
        }
    }
    
    /**
     * 
     * @ORM\PostRemove()
     */
    public function postRemove()
    {
        if ($this->getOrgCode()) {
            @unlink(__DIR__.'/../../../../web/uploads/corp/' . $this->getOrgCode());
        }
        
        if ($this->getLicense()) {
            @unlink(__DIR__.'/../../../../web/uploads/corp/' . $this->getLicense());
        }
        
        if ($this->getPledge()) {
            @unlink(__DIR__.'/../../../../web/uploads/corp/' . $this->getPledge());
        }
    }
    
    /**
     * Set phone
     *
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set legalPerson
     *
     * @param string $legalPerson
     */
    public function setLegalPerson($legalPerson)
    {
        $this->legalPerson = $legalPerson;
    }

    /**
     * Get legalPerson
     *
     * @return string 
     */
    public function getLegalPerson()
    {
        return $this->legalPerson;
    }

    /**
     * Set orgCode
     *
     * @param string $orgCode
     */
    public function setOrgCode($orgCode)
    {
        $this->orgCode = $orgCode;
    }

    /**
     * Get orgCode
     *
     * @return string 
     */
    public function getOrgCode()
    {
        return $this->orgCode;
    }

    /**
     * Set license
     *
     * @param string $license
     */
    public function setLicense($license)
    {
        $this->license = $license;
    }

    /**
     * Get license
     *
     * @return string 
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * Set contactId
     *
     * @param string $contactId
     */
    public function setContactId($contactId)
    {
        $this->contactId = $contactId;
    }

    /**
     * Get contactId
     *
     * @return string 
     */
    public function getContactId()
    {
        return $this->contactId;
    }

    /**
     * Set pledge
     *
     * @param string $pledge
     */
    public function setPledge($pledge)
    {
        $this->pledge = $pledge;
    }

    /**
     * Get pledge
     *
     * @return string 
     */
    public function getPledge()
    {
        return $this->pledge;
    }

    /**
     * Set user
     *
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return User 
     */
    public function getUser()
    {
        return $this->user;
    }
    
    public function __toString()
    {
        return $this->name;
    }
}