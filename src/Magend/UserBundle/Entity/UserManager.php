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
    
    /**
     * Find users with id in $ids
     * Return array of User entites if not forDisplay,
     * or array of array, which contains:
     * 'avatarUrl'
     * 'id'
     * 'username'
     * 'nickname'
     * 'gender'
     * 
     * 
     * @param array $ids
     * @param bool $retObjs
     * @return array
     */
    public function findUsersById($ids, $retObjs = false)
    {
        /*
        $keyedUsers = null;
        $users = null;
        $uids = array_values($ids);
        if ($retObjs) {
            $users = $this->repository->findBy(array(
                'id' => $uids
            ));
            if (is_array($users)) {
                foreach ($users as $user) {
                    $keyedUsers[$user->getId()] = $user;
                }
            }
        } else {
            $qb = $this->em->createQueryBuilder();
            $q = $qb->select('u.id, u.avatar, u.username, u.nickname, u.gender, u.updated_at')
                    ->from('StoryUserBundle:User', 'u')
                    ->where('u.id in(:uids)')
                    ->setParameters(array('uids' => $uids))
                    ->getQuery();
            $users = $q->getArrayResult();
            // @todo refactor this with User::getAvatarUrl
            $avatarDir = self::$container->getParameter('story_user.avatar_dir') . '/';
            foreach ($users as &$user) {
                $user['avatarUrl'] = $user['avatar'] ? $avatarDir . $user['avatar'] : null;
                if (empty($user['nickname'])) {
                	$user['nickname'] = $user['username'];
                }
                unset($user['updated_at']);
                
                $keyedUsers[$user['id']] = $user;
            }
        }
        return $keyedUsers;*/
    }
}