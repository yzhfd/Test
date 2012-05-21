<?php

namespace Magend\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Magend\UserBundle\Entity\User
 *
 * @ORM\Table(name="mag_user")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class User extends BaseUser
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * Nickname
     * repeatable, and can be Unicode
     * 
     * @var String
     * 
     * @ORM\Column(name="nickname", type="string", length=15)
     * @Assert\NotBlank(message="昵称不能为空", groups={"Registration", "Profile"})
     * @Assert\MinLength(limit="2", message="昵称不能少于2个字符", groups={"Registration", "Profile"})
     * @Assert\MaxLength(limit="15", message="昵称不能多于12个字符", groups={"Registration", "Profile"})
     */
    private $nickname;

    /**
     * @var string $avatar
     *
     * @ORM\Column(name="avatar", type="string", length=255, nullable=true)
     */
    private $avatar;
    
    /**
     * 
     * @Assert\Image(
     *     mimeTypesMessage = "不是有效图片",
     *     maxSize = "2000000",
     *     maxSizeMessage = "图片大小需小于2M"
     * )
     */
    public $avatarFile;

    /**
     * Mobile number
     * 
     * @var string
     * @ORM\Column(name="mobile", type="string", length=12, nullable=true)
     */
    public $mobile;
    
    // @todo weibo
    
    
    /**
     * The time user updated its profile
     * Use this to avoid changing doctrine's tracking policy
     * 
     * @var datetime
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;
    
    /**
     * 
     * @var datetime
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */    
    private $createdAt;
    
    /**
     * //, inversedBy="users"
     * 
     * @ORM\ManyToMany(targetEntity="Group")
     * @ORM\JoinTable(
     * 		name="mag_user_group",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $groups;
    
    //Corp user properties//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    /**
     * Phone number
     * 
     * @var string
     * @ORM\Column(name="phone", type="string", length=64, nullable=true)
     */
    private $phone;
    
    /**
     * Company name
     * 
     * @var string
     * @ORM\Column(name="corp_name", type="string", length=128, nullable=true)
     */
    private $corpName;
    
    /**
     * Corporate legal person's name
     * 
     * @var string
     * @ORM\Column(name="corp_legal", type="string", length=32, nullable=true)
     */
    private $corpLegal;
    
    /**
     * Code
     * 
     * @var string
     * @ORM\Column(name="corp_code", type="string", length=255, nullable=true)
     */
    private $corpCode;
    
    /**
     * 
     * @var UploadedFile
     */
    public $corpCodeFile;
    
    /**
     * License
     * 
     * @var string
     * @ORM\Column(name="corp_license", type="string", length=255, nullable=true)
     */
    private $corpLicense;
    
    /**
     * 
     * @var UploadedFile
     */
    public $corpLicenseFile;
    
    /**
     * Identity of corporate contact person
     * 
     * @var string
     * @ORM\Column(name="corp_contactId", type="string", length=255, nullable=true)
     */
    private $corpContactId;
    
    /**
     * Pledge not to do anything evil
     * 
     * @var string
     * @ORM\Column(name="corp_pledge", type="string", length=255, nullable=true)
     */
    private $corpPledge;
    
    /**
     * 
     * @var UploadedFile
     */
    public $corpPledgeFile;
    
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
        $this->createdAt = new \DateTime();
    }

    /**
     * 
     * @ORM\PreUpdate()
     * @return void
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime;
    }
    
    private function getAvatarDir()
    {
        return __DIR__.'/../../../../web/uploads/avatars/';
    }
    
    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($this->getAvatar() == null) {
            return;
        }
        
        $container = UserManager::$container;
        if (is_file($file = $this->getAvatarDir() . $this->getAvatar())) {
            @unlink($file);
        }
    }

    /**
     * @return \DateTime
     */
    public function getCredentialsExpireAt()
    {
        return $this->credentialsExpireAt;
    }

    /**
     * @param \DateTime|null $date
     * @return void
     */
    public function setCredentialsExpireAt(\DateTime $date = null)
    {
        $this->credentialsExpireAt = $date;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getUsername() ?: '-';
    }

    /**
     * Set related groups
     *
     * @param aarrat $groups
     */
    public function setGroups($groups)
    {
        foreach ($groups as $group){
            $this->addGroup($group);
        }
    }
    
    /**
     * Set avatar
     *
     * @param string $avatar
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * Get avatar
     *
     * @return string 
     */
    public function getAvatar()
    {
        return $this->avatar;
    }
    
    public function getMobile()
    {
        return $this->mobile;
    }
    
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
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
    
    public function getCorpName()
    {
        return $this->corpName;
    }
    
    public function setCorpName($corpName)
    {
        $this->corpName = $corpName;
    }
    
    /**
     * Set nickname
     *
     * @param string $nickname
     */
    public function setNickname($nickname)
    {
        $this->nickname = $nickname;
    }

    /**
     * Get nickname
     *
     * @return string 
     */
    public function getNickname()
    {
        return $this->nickname;
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
     * Set corpLegal
     *
     * @param string $corpLegal
     */
    public function setCorpLegal($corpLegal)
    {
        $this->corpLegal = $corpLegal;
    }

    /**
     * Get corpLegal
     *
     * @return string 
     */
    public function getCorpLegal()
    {
        return $this->corpLegal;
    }

    /**
     * Set corpCode
     *
     * @param string $corpCode
     */
    public function setCorpCode($corpCode)
    {
        $this->corpCode = $corpCode;
    }

    /**
     * Get corpCode
     *
     * @return string 
     */
    public function getCorpCode()
    {
        return $this->corpCode;
    }

    /**
     * Set corpLicense
     *
     * @param string $corpLicense
     */
    public function setCorpLicense($corpLicense)
    {
        $this->corpLicense = $corpLicense;
    }

    /**
     * Get corpLicense
     *
     * @return string 
     */
    public function getCorpLicense()
    {
        return $this->corpLicense;
    }

    /**
     * Set corpContactId
     *
     * @param string $corpContactId
     */
    public function setCorpContactId($corpContactId)
    {
        $this->corpContactId = $corpContactId;
    }

    /**
     * Get corpContactId
     *
     * @return string 
     */
    public function getCorpContactId()
    {
        return $this->corpContactId;
    }

    /**
     * Set corpPledge
     *
     * @param string $corpPledge
     */
    public function setCorpPledge($corpPledge)
    {
        $this->corpPledge = $corpPledge;
    }

    /**
     * Get corpPledge
     *
     * @return string 
     */
    public function getCorpPledge()
    {
        return $this->corpPledge;
    }
}