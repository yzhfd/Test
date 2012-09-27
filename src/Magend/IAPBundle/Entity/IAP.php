<?php

namespace Magend\IAPBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Magend\IAPBundle\Entity\IAP
 *
 * @ORM\Table(name="mag_iap")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class IAP
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
     * @var Issue
     * 
     * @ORM\OneToOne(
     *     targetEntity="Magend\IssueBundle\Entity\Issue",
     *     inversedBy="iap"
     * )
     */
    private $issue;

    /**
     * @var datetime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var datetime $distributedAt
     *
     * @ORM\Column(name="distributed_at", type="datetime", nullable=true)
     */
    private $distributedAt;

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
     */
    public function prePersist()
    {
        $this->createdAt = new DateTime;
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
     * Set distributedAt
     *
     * @param datetime $distributedAt
     */
    public function setDistributedAt($distributedAt)
    {
        $this->distributedAt = $distributedAt;
    }

    /**
     * Get distributedAt
     *
     * @return datetime 
     */
    public function getDistributedAt()
    {
        return $this->distributedAt;
    }

    /**
     * Set issue
     *
     * @param Issue $issue
     */
    public function setIssue($issue)
    {
        $this->issue = $issue;
    }

    /**
     * Get issue
     *
     * @return Issue 
     */
    public function getIssue()
    {
        return $this->issue;
    }
}