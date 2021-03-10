<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\ResetPasswordRequestRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

/**
 * @Route("/reset-password")
 */
class ResetPasswordController extends AbstractController
{
    /**
     * @param string $token
     * @Route ("", name="app_forgotten_password")
     */
   public function forgotPassword(Request $request,
                                  EntityManagerInterface $entityManager,
                                  UserRepository $userRepository,
                                  \Swift_Mailer $mailer,
                                  TokenGeneratorInterface  $tokenGenerator)
   {
       $formRequest = $this->createForm(ResetPasswordRequestFormType::class);

       $formRequest->handleRequest($request);

       if ($formRequest->isSubmitted() && $formRequest->isValid()){
           //on recupere les donnees
           $data = $formRequest->getData();

           //on cherche si un user a cet email
           $user = $userRepository->findOneBy(['email' => $data]);

           if(!$user){
               $this->addFlash('danger','cette adresse mail n\'existe pas');

               return $this->redirectToRoute('app_forgotten_password');
           }

           //on génère un token
           $token = $tokenGenerator->generateToken();

           try{
               $user->setResetToken($token);
               $entityManager->persist($user);
               $entityManager->flush();
           }catch (\Exception $e){
               $this->addFlash('warning','Une erreur est survenue : '. $e->getMessage());
               return $this->redirectToRoute('app_forgotten_password');
           }

           //generation de l'url de réinitialisation de mot de passe

           $url = $this->generateUrl('app_reset_password', ['token' => $token]);

           $message = (new \Swift_Message('Réinitialisation de votre mdp'))
               ->setFrom('enisortirproject@gmail.com')
               ->setTo($user->getEmail())
               ->setBody($this->renderView('reset_password/email.html.twig', ['url'=>$url]));
           //on envoi l'email
           $mailer->send($message);
           $this->addFlash('message','un e-mail de réinitialisation de mot de passe vous a été envoyé');

           return $this->redirectToRoute('app_login');
       }

       //on envoie vers la page de demande de l'email

       return $this->render('reset_password/request.html.twig', [
           'formRequest'=> $formRequest->createView(),

       ]);
   }

    /**
     * @Route ("/reset/{token}", name="app_reset_password")
     */
   public function resetPassword($token, Request $request, EntityManagerInterface  $entityManager)
   {
       $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['reset_token' => $token]);

       if (!$user){
           $this->addFlash('danger','Token inconnu');
           return $this->redirectToRoute('app_login');
       }

       $formReset = $this->createForm(ChangePasswordFormType::class);

       $formReset->handleRequest($request);

       $plainPassword = $formReset->get('plainPassword')->getData();

       //si le formulaire est envoyé en méthode POST
       if ($formReset->isSubmitted() && $formReset->isValid()){


            $user->setResetToken(null);

            $user->setPassword($plainPassword);
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success','Mot de passe modifié avec succès');

            return $this->redirectToRoute('app_login');

       }

       return $this->render('reset_password/reset.html.twig', [
           'formReset'=> $formReset->createView(),
       ]);
   }
}
