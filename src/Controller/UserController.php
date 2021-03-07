<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Entity\User;
use App\Form\ModifyProfileType;
use App\Form\PictureType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
}
