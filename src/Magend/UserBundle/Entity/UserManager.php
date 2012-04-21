<?php

namespace Magend\UserBundle\Entity;

use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\UserBundle\Entity\UserManager as BaseUserManager;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserManager extends BaseUserManager
{
	public static $container;

	/**
	 * This method is used internally by symfony2
	 * Here is overwritten
	 * 
	 * @see Model/FOS\UserBundle\Model.UserManager::loadUserByUsername()
	 */
    public function loadUserByUsername($username)
    {
    	// @todo all digits then findUserByMobilephone()
    	$user = $this->findUserByUsernameOrEmail($username);
        if (!$user) {
            throw new UsernameNotFoundException('用户不存在');
        }

        return $user;
    }
    
    /**
     * Method inject service container
     * 
     * @param ConatainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
    	self::$container = $container;
    }
    
    /** 
     * Find user by its id
     * 
     * @param int $userId
     * @return User
     */
    public function findUserById($userId)
    {
        return $this->repository->find($userId);
    }
}