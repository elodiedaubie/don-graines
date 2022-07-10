<?php

namespace App\Service;

use App\Entity\User;
use App\Security\EmailVerifier;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MailerManager extends AbstractController
{
    private MailerInterface $mailerInterface;
    private EmailVerifier $emailVerifier;

    public function __construct(
        MailerInterface $mailerInterface,
        EmailVerifier $emailVerifier
    ) {
        $this->mailerInterface = $mailerInterface;
        $this->emailVerifier = $emailVerifier;
    }

    /**
     *  mail sent after the registration, with link to confirm e-mail adress
     */
    public function sendVerifyRegistration(User $user): void
    {
        // generate a signed url and email it to the user
        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->from(new Address('noreply@grainesenlair.com', 'Graines en l\'air'))
                ->to($user->getEmail())
                ->subject('Veuillez confirmer votre email')
                ->htmlTemplate('email/confirmation_email.html.twig')
        );

        $this->addFlash(
            'warning',
            'Un email va vous être envoyé afin de finaliser votre inscription.'
        );
    }
}
