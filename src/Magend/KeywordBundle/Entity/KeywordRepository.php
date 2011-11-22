<?php

namespace Magend\KeywordBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * KeywordRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class KeywordRepository extends EntityRepository
{
    /**
     * Convert array of string to array of entities
     * 
     * @param Array $keywords
     * @return array
     */
    public function toEntities(Array $keywords)
    {
        $entities = $this->findBy(array('keyword'=>$keywords));
        $existingKws = array();
        foreach ($entities as $entity) {
            $existingKws[] = $entity->getKeyword();
        }
        foreach ($keywords as $kw) {
            if (!in_array($kw, $existingKws)) {
                $entities[] = new Keyword($kw);
            }
        }
        
        return $entities;
    }
}