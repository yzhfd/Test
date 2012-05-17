<?php

namespace Magend\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Magend\UserBundle\Entity\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Magend\UserBundle\Entity\CorpUser
 *
 * @ORM\Table(name="mag_corpuser")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class CorpUser extends BaseUser
{
    /**
     * phone
     * 
     * @var string
     * @ORM\Column(name="phone", type="string", length=64, nullable=true)
     */
    private $phone;
}