<?php

namespace Magend\UserBundle\Controller;

use FOS\UserBundle\Controller\RegistrationController as BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class RegistrationController extends BaseController
{
    /**
     * 
     * @todo refactor - move it out
     * @param Form $form
     */
    private function getErrorMessages($form) {
        $errors = array();
        foreach ($form->getErrors() as $key => $error) {
            // translation 
            $errors[] = $this->container->get('translator')->trans(
                $error->getMessageTemplate(),
                $error->getMessageParameters(),
                'validators'
            );
        }
        if ($form->hasChildren()) {
            foreach ($form->getChildren() as $child) {
                if (!$child->isValid()) {
                    $errors = array_merge($errors, $this->getErrorMessages($child));
                }
            }
        }
        return $errors;
    }
    
    public function registerAction()
    {
        $form = $this->container->get('fos_user.registration.form');
        $formHandler = $this->container->get('fos_user.registration.form.handler');
        $confirmationEnabled = $this->container->getParameter('fos_user.registration.confirmation.enabled');
        
        $sess = $this->container->get('session');
        $process = $formHandler->process($confirmationEnabled);
        if ($process) {
            $user = $form->getData();
            if ($confirmationEnabled) {
                $this->container->get('session')->set('fos_user_send_confirmation_email/email', $user->getEmail());
            } else {
                $user->addRole('ROLE_ADMIN');
                $this->container->get('doctrine.orm.entity_manager')->flush();
                $this->authenticateUser($user);
            }
            
            if ($sess->get('in_web')) {
                return new RedirectResponse($this->container->get('router')->generate('home'));
            } else {
                return $this->container->get('templating')->renderResponse('MagendUserBundle:User:user.xml.twig');
            }
        } else if ($this->container->get('request')->getMethod() == 'POST') {
            $errors = $this->getErrorMessages($form);
            return $this->container->get('templating')->renderResponse(
                'MagendUserBundle:Registration:regerr.xml.twig',
                array('errors' => $errors)
            );
        }
        
        $this->container->get('session')->set('in_web', true);
        return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:register.html.'.$this->getEngine(), array(
            'form' => $form->createView(),
            'theme' => $this->container->getParameter('fos_user.template.theme'),
        ));
    }
}
