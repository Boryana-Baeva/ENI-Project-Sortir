<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    /**
     * @Route("/profile/{id}", name="user_profile")
     */
    public function show($id, EntityManagerInterface  $em): Response
    {
        $userRepo = $em->getRepository(User::class);
        $user = $userRepo->find($id);

        return $this->render('user/profile.html.twig', [
            "user"=> $user
        ]);
    }

    /**
     * @Route ("/profile/modify/{id}, name="user_modify")
     */
    public function modify($id, EntityManagerInterface $em)
    {
        $userRepo = $em->getRepository(User::class);
        $user = $userRepo->find($id);



        return $this->render('user/modifyProfile.html.twig', [
            "user"=> $user
        ]);
    }
}
