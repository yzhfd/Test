<?php

namespace Magend\UserBundle\Controller;

use FOS\UserBundle\Controller\RegistrationController as BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * CorpRegistrationController
 * 
 * @Route("/corp")
 * @author kail
 */
class CorpRegistrationController extends BaseController
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
    
    /**
     * 
     * @Route("/register", name="corp_registration")
     * @Template()
     */
    public function registerAction()
    {
        $form = $this->container->get('magend.corp_registration.form');
        $formHandler = $this->container->get('magend.corp_registration.form.handler');
        $confirmationEnabled = $this->container->getParameter('fos_user.registration.confirmation.enabled');

        $process = $formHandler->process($confirmationEnabled);
        if ($process) {
            $user = $form->getData();
            if ($confirmationEnabled) {
                $this->container->get('session')->set('fos_user_send_confirmation_email/email', $user->getEmail());
            } else {
                $this->authenticateUser($user);
            }
            
            return $this->redirect($this->generateUrl('home'));
        }
        
        return array(
            'form' => $form->createView(),
        );
    }
}
