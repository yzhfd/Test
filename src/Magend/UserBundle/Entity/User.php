<?php

namespace Magend\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\Common\Collections\ArrayCollection;

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
     * OneToOne cannot be lazy loaded,
     * fetch LAZY is actually default
     * 
     * @ORM\OneToOne(
     *     targetEntity="UserCorp",
     *     cascade={"persist", "remove"},
     *     inversedBy="user",
     *     fetch="LAZY"
     * )
     * @ORM\JoinColumn(name="corp_id")
     * @var UserCorp
     */
    private $corp;
    
    /**
     * Magzines that this user have rights to edit
     *
     * @var Magzine
     * @ORM\ManyToMany(targetEntity="Magend\MagzineBundle\Entity\Magzine", mappedBy="staffUsers")
     */
    private $grantedMags;
    
    /**
     *
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(
     *     name="boss_id",
     *     referencedColumnName="id",
     *     onDelete="CASCADE"
     * )
     */
    private $boss;
    
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
        parent::__construct(); // !important
        $this->grantedMags = new ArrayCollection();
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
    
    public function getCorp()
    {
        return $this->corp;
    }
    
    public function setCorp($corp)
    {
        $this->corp = $corp;
    }
    
    public function getBoss()
    {
        return $this->boss;
    }
    
    public function setBoss($boss)
    {
        $this->boss = $boss;
    }
    
    public function getGrantedMags()
    {
        return $this->grantedMags;
    }
    
    public function addGrantedMag($mag)
    {
        $this->grantedMags[] = $mag;
    }
}