<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\CaptchaFormType;
use App\Form\RegistrationFormType;
use App\Security\AppCustomAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Constraint\StringContains;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Gregwar\Captcha\CaptchaBuilder;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, 
                                AppCustomAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        $captcha = new CaptchaBuilder;
        $captcha->build();
        $captcha->save('captcha.jpg');
        $captchaform = $this->createForm(CaptchaFormType::class);
        $captchaform->handleRequest($request);
        
        if ($captchaform->isSubmitted() && $captcha->getPhrase()==$captchaform->get('captchaCode')) {
            if ($form->isSubmitted() && $form->isValid()) {
                $user->setPassword(
                $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                
                $entityManager->persist($user);
                $entityManager->flush();

                return $userAuthenticator->authenticateUser(
                    $user,
                    $authenticator,
                    $request
                );
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'captchaForm' => $captchaform->createView(),
        ]);
    }
}
