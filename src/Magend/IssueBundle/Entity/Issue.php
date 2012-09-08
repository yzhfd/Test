<?php

namespace Magend\IssueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Magend\ArticleBundle\Entity\Article;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Magend\IssueBundle\Entity\Issue
 *
 * @ORM\Table(name="mag_issue")
 * @ORM\Entity(repositoryClass="Magend\IssueBundle\Entity\IssueRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Issue
{
    const PUBLISH_PREVIEW  = 0;
    const PUBLISH_OFFICIAL = 1;
    
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;
    
    /**
     * Name of audio file on the server
     * 
     * @var string $audio
     * 
     * @ORM\Column(name="audio", type="string", length=255, nullable=true)
     */
    private $audio;
    
    /**
     * 
     * @var File
     */
    public $audioFile;
    
    /**
     * 
     * @var string $poster
     * @ORM\Column(name="poster", type="string", length=255, nullable=true)
     */
    private $poster;
    
    /**
     * 
     * @var File
     */
    public $posterFile;
    
    /**
     * 
     * @var string
     * @ORM\Column(name="video", type="string", length=255, nullable=true)
     */
    private $video;
    
    /**
     * 
     * @var File
     */
    public $videoFile;
    
    /**
     * @var string $landscapeCover
     *
     * @ORM\Column(name="landscape_cover", type="string", length=255, nullable=true)
     */
    private $landscapeCover;
    
    /**
     * 
     * @var File
     */
    public $landscapeCoverFile;
    
    /**
     * @var string $portraitCover
     *
     * @ORM\Column(name="portrait_cover", type="string", length=255, nullable=true)
     */
    private $portraitCover;
    
    /**
     *
     * @var File
     */
    public $portraitCoverFile;

    /**
     * Comma separated article ids
     * used to maitain the order of articles in this issue
     * 
     * @var string $articleIds
     *
     * @ORM\Column(name="article_ids", type="text", nullable=true)
     */
    private $articleIds;
    
    /**
     * Normally one article can only belong to one issue
     * But we don't need limit this
     * 
     * cascade will not happen unless you define it
     * cascade={"persist", "remove"}
     * 
     * indexBy here is important but will affect in condition in DQL
     * 
     * @ORM\ManyToMany(
     *     targetEntity="Magend\ArticleBundle\Entity\Article",
     *     inversedBy="issues",
     *     indexBy="id",
     *     fetch="EXTRA_LAZY"
     * )
     * @ORM\JoinTable(name="mag_issue_article")
     */
    private $articles;

    /**
     * @todo use a field or compute from articles
     * @var integer $nbPages
     *
     * @ORM\Column(name="nb_pages", type="integer")
     */
    private $nbPages = 0;

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
     * For test or official release
     * 
     * @var integer
     * @ORM\Column(name="publish_mode", type="integer", nullable=true)
     */
    private $publishMode;

    /**
     * @var integer $publisher
     *
     * @ORM\Column(name="publisher", type="integer")
     */
    private $publisher = 0;
    
    /**
     * @var date $publishedAt
     *
     * @ORM\Column(name="published_at", type="date", nullable=true)
     */
    private $publishedAt;

    /**
     * @var integer $nbFaved
     *
     * @ORM\Column(name="nb_faved", type="integer")
     */
    private $nbFaved = 0;

    /**
     * @var integer $nbDownloaded
     *
     * @ORM\Column(name="nb_downloaded", type="integer")
     */
    private $nbDownloaded = 0;
    
    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }
    
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
        
        if ($this->landscapeCoverFile) {
            $imgName = uniqid('landscape_') . '.' . $this->landscapeCoverFile->guessExtension();
            $this->landscapeCoverFile->move(__DIR__.'/../../../../web/uploads/', $imgName);
            
            if ($this->getLandscapeCover()) {
                @unlink(__DIR__.'/../../../../web/uploads/' . $this->getLandscapeCover());
            }
            
            $this->setLandscapeCover($imgName);
        }
        
        if ($this->portraitCoverFile) {
            $imgName = uniqid('portrait_') . '.' . $this->portraitCoverFile->guessExtension();
            $this->portraitCoverFile->move(__DIR__.'/../../../../web/uploads/', $imgName);
        
            if ($this->getPortraitCover()) {
                @unlink(__DIR__.'/../../../../web/uploads/' . $this->getPortraitCover());
            }
        
            $this->setPortraitCover($imgName);
        }
        
        if ($this->audioFile) {
            $fileName = uniqid('audio_') . '.' . $this->audioFile->guessExtension();
            $this->audioFile->move(__DIR__.'/../../../../web/uploads/', $fileName);
            
            if ($this->getAudio()) {
                @unlink(__DIR__.'/../../../../web/uploads/' . $this->getAudio());
            }
        
            $this->setAudio($fileName);
        }
        
        if ($this->posterFile) {
            $fileName = uniqid('poster_') . '.' . $this->posterFile->guessExtension();
            $this->posterFile->move(__DIR__.'/../../../../web/uploads/', $fileName);
            
            if ($this->getPoster()) {
                @unlink(__DIR__.'/../../../../web/uploads/' . $this->getPoster());
            }
        
            $this->setPoster($fileName);
        }
        
        if ($this->videoFile) {
            $fileName = uniqid('video_') . '.' . $this->videoFile->guessExtension();
            $this->videoFile->move(__DIR__.'/../../../../web/uploads/', $fileName);
        
            if ($this->getVideo()) {
                @unlink(__DIR__.'/../../../../web/uploads/' . $this->getVideo());
            }
        
            $this->setVideo($fileName);
        }
    }
    
    /**
     * 
     * @ORM\PostRemove()
     */
    public function postRemove()
    {
        if ($this->getPortraitCover()) {
            @unlink(__DIR__.'/../../../../web/uploads/' . $this->getPortraitCover());
        }
        if ($this->getLandscapeCover()) {
            @unlink(__DIR__.'/../../../../web/uploads/' . $this->getLandscapeCover());
        }
        if ($this->getAudio()) {
            @unlink(__DIR__.'/../../../../web/uploads/' . $this->getAudio());
        }
        if ($this->getVideo()) {
            @unlink(__DIR__.'/../../../../web/uploads/' . $this->getVideo());
        }
        if ($this->getPoster()) {
            @unlink(__DIR__.'/../../../../web/uploads/' . $this->getPoster());
        }
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Set audio
     *
     * @param string $audio
     */
    public function setAudio($audio)
    {
        $this->audio = $audio;
    }

    /**
     * Get audio
     *
     * @return string 
     */
    public function getAudio()
    {
        return $this->audio;
    }

    public function setVideo($video)
    {
        $this->video = $video;
    }
    
    public function getVideo()
    {
        return $this->video;
    }
    
    public function setPoster($poster)
    {
        $this->poster = $poster;
    }
    
    public function getPoster()
    {
        return $this->poster;
    }
    
    /**
     * Set landscape cover
     *
     * @param string $landscapeCover
     */
    public function setLandscapeCover($landscapeCover)
    {
        $this->landscapeCover = $landscapeCover;
    }

    /**
     * Get landscape cover
     *
     * @return string 
     */
    public function getLandscapeCover()
    {
        return $this->landscapeCover;
    }
    
    /**
     * Set portrait cover
     *
     * @param string $portraitCover
     */
    public function setPortraitCover($portraitCover)
    {
        $this->portraitCover = $portraitCover;
    }

    /**
     * Get portrait cover
     *
     * @return string 
     */
    public function getPortraitCover()
    {
        return $this->portraitCover;
    }

    /**
     * Set articleIds
     *
     * @param array|string $articleIds
     */
    public function setArticleIds($articleIds)
    {
        // if consistency is important, then fetch articles and check
        if (is_array($articleIds)) {
            $articleIds = implode(',', $articleIds);
        }
        $this->articleIds = $articleIds;
    }
    
    /**
     * The usual way is create article first, and then
     * attach it to one issue(and reorder)
     * 
     * @param Article $article
     */
    public function addArticle(Article $article)
    {
        if ($this->articles->contains($article)) {
            return;
        }
                
        $this->articles->add($article);
        
        $articleIds = $this->getArticleIds();
        if (!in_array($article->getId(), $articleIds)) {
            $articleIds[] = $article->getId();
            $this->setArticleIds($articleIds);
        }
    }
    
    public function removeArticle(Article $article)
    {
        $this->articles->removeElement($article);
        
        $articleIds = $this->getArticleIds();
        $newArticleIds = array();
        foreach ($articleIds as $articleId) {
            if ($articleId != $article->getId()) {
                $newArticleIds[] = $articleId;
            }
        }
        $this->setArticleIds($newArticleIds);
    }

    /**
     * Get articleIds
     *
     * @return string 
     */
    public function getArticleIds()
    {
        return $this->articleIds ? explode(',', trim($this->articleIds, ',')) : array();
    }
    
    /**
     * 
     * @param bool $withOrder the order specified in $articleIds
     */
    public function getArticles($withOrder = true)
    {
        $articles = array();
        $articleIds = $this->getArticleIds();
        if (!empty($articleIds)) {
            foreach ($articleIds as $articleId) {
                if (!empty($this->articles[$articleId])) {
                    $articles[$articleId] = $this->articles[$articleId];
                }
            }
        }
        
        return !empty($articles) ? $articles : $this->articles;
    }

    /**
     * Get nbArticles
     *
     * @return integer 
     */
    public function getNbArticles()
    {
        return count($this->getArticleIds());
    }

    /**
     * Set nbPages
     *
     * @param integer $nbPages
     */
    public function setNbPages($nbPages)
    {
        $this->nbPages = $nbPages;
    }

    /**
     * Get nbPages
     *
     * @return integer 
     */
    public function getNbPages()
    {
        return $this->nbPages;
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

    /**
     * Set publisher
     *
     * @param integer $publisher
     */
    public function setPublisher($publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * Get publisher
     *
     * @return integer 
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * Set nbFaved
     *
     * @param integer $nbFaved
     */
    public function setNbFaved($nbFaved)
    {
        $this->nbFaved = $nbFaved;
    }

    /**
     * Get nbFaved
     *
     * @return integer 
     */
    public function getNbFaved()
    {
        return $this->nbFaved;
    }

    /**
     * Set nbDownloaded
     *
     * @param integer $nbDownloaded
     */
    public function setNbDownloaded($nbDownloaded)
    {
        $this->nbDownloaded = $nbDownloaded;
    }

    /**
     * Get nbDownloaded
     *
     * @return integer 
     */
    public function getNbDownloaded()
    {
        return $this->nbDownloaded;
    }
    
    /**
     * Set publishedAt
     *
     * @param datetime $publishedAt
     */
    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;
    }

    /**
     * Get publishedAt
     *
     * @return datetime 
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }
    
    public function getPublishMode()
    {
        return $this->publishMode === null ? self::PUBLISH_PREVIEW : $this->publishMode;
    }
    
    public function setPublishMode($publishMode)
    {
        $this->publishMode = $publishMode;
    }
    
    public function isPublishedForPreview()
    {
        return $this->publishMode === self::PUBLISH_PREVIEW;
    }
    
    public function isPublishedOfficially()
    {
        return $this->publishMode === self::PUBLISH_OFFICIAL;
    }
}