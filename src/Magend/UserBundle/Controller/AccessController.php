<?php

namespace Magend\UserBundle\Controller;

use Exception;
use DateTime;
use Magend\BaseBundle\Controller\BaseController as Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Magend\UserBundle\Entity\User;

/**
 * Manager logins from mobile and other non-web-browser ways
 * 
 * @Route("/access")
 * @author Kail
 */
class AccessController extends Controller
{
    /**
     * RESTful login
     * @Route("/login", name="access_login")
     */
    public function loginAction()
    {
        $req = $this->getRequest();
        $username = $req->get('username', '');
        $password = $req->get('password', '');
        $providerKey = $this->container->getParameter('fos_user.firewall_name'); 
        $token = new UsernamePasswordToken($username, $password, $providerKey);
        
        $error = null;
        try {
            // token will be authenticated token on success
            $token = $this->get('security.authentication.manager')->authenticate($token);
        } catch (Exception $e) {
            $error = $this->get('translator')->trans($e->getMessage());
        }
        
        if ($error === null) {
            $user = $token->getUser();
            if ($user instanceof User) {
                // @todo from which device
                $user->setLastLogin(new DateTime());
                $um = $this->get('magend.user_manager');
                $um->updateUser($user);
                
                $this->get("security.context")->setToken($token);
                return $this->container->get('templating')->renderResponse('MagendUserBundle:User:user.xml.twig');
            }
            $error = '非消费者帐户';
        }
        
        return $this->container->get('templating')->renderResponse(
            'MagendUserBundle:Security:loginfail.xml.twig',
            array('error' => $error));
    }
    
    /**
     * Logout
     * 
     * @Route("/logout", name="access_logout")
     */
    public function logoutAction()
    {
        // REST is stateless
        //$this->get("request")->getSession()->invalidate();
        //$this->get("security.context")->setToken(null);
        
        // @todo remove token
    }
}
