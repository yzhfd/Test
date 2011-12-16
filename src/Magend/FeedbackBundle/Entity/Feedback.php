<?php

namespace Magend\FeedbackBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Magend\FeedbackBundle\Entity\Feedback
 *
 * @ORM\Table(name="mag_feedback")
 * @ORM\Entity
 */
class Feedback
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}