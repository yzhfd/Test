<?php

namespace Magend\UserBundle\Form\Handler;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Mailer\MailerInterface;

class CorpRegistrationFormHandler
{
    protected $request;
    protected $userManager;
    protected $form;
    protected $mailer;

    public function __construct(Form $form, Request $request, UserManagerInterface $userManager, MailerInterface $mailer)
    {
        $this->form = $form;
        $this->request = $request;
        $this->userManager = $userManager;
        $this->mailer = $mailer;
    }

    public function process($confirmation = null)
    {
        $user = $this->userManager->createUser();
        $this->form->setData($user);

        if ('POST' == $this->request->getMethod()) {
            $this->form->bindRequest($this->request);

            if ($this->form->isValid()) {
                if (true === $confirmation) {
                    $user->setEnabled(false);
                    $this->mailer->sendConfirmationEmailMessage($user);
                } else if (false === $confirmation) {
                    $user->setConfirmationToken(null);
                    $user->addRole('ROLE_CORP');
                    // $user->setEnabled(true);
                }

                $this->userManager->updateUser($user);

                return true;
            }
        }

        return false;
    }
}
