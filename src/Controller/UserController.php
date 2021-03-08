<?php
//comment
namespace App\Controller;

use App\Entity\Picture;
use App\Entity\User;
use App\Form\ModifyProfileType;
use App\Form\PictureType;
use App\Form\RegistrationFormType;
use App\Form\UserType;
use App\Security\LoginAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class UserController extends AbstractController
{

    /**
     * @Route("/profile/{id}", name="user_profile")
     */
    public function show($id, EntityManagerInterface  $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $userRepo = $em->getRepository(User::class);
        $user = $userRepo->find($id);
        $pic = $user->getProfilePicture();



        return $this->render('user/profile.html.twig', [
            "user"=> $user,
            "profilePicture"=>$pic

        ]);
    }

    /**
     * @Route ("/profile/modify/{id}", name="user_modify")
     */
    public function modify($id, EntityManagerInterface $em, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if($this->getUser()->getId() != $id)
        {
            throw new AccessDeniedException('Cannot edit other people profiles');
        }

        $userRepo = $em->getRepository(User::class);
        $user = $userRepo->find($id);


        $modifyForm = $this->createForm(ModifyProfileType::class, $user);
        $modifyForm->handleRequest($request);


        if ($modifyForm->isSubmitted() && $modifyForm->isValid())
        {
            //Récupération de l'image transmise
                $profilePicture = $modifyForm['picture']->getData();

            if ($profilePicture){

                //génération d'un nouveau nom
                $newName =md5(uniqid()).'.'.$profilePicture->guessExtension();


                $profilePicture->move(
                    $this->getParameter('pictures_directory'),
                    $newName
                );

                $user->setProfilePicture($newName);
            }


            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('user_profile', ['id' => $user->getId()]);
        }

        return $this->render('user/modifyProfile.html.twig', [
            "user"=> $user,
            "modifyForm" => $modifyForm->createView()
        ]);
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginAuthenticator $authenticator): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $user = new User();
        $user->setAdmin(false);
        $user->setActive(true);
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/user/add", name="user_add")
     */
    public function add(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {

        $user = new User();
        $user->setAdmin(false);
        $user->setActive(true);
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_profile', [
                "id" => $user->getId()
            ]);
        }

        return $this->render('user/add.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
