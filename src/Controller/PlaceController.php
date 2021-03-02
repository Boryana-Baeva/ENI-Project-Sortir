<?php

namespace App\Controller;

use App\Entity\Place;
use App\Form\PlaceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlaceController extends AbstractController
{

    /**
     * @Route("/place/add", name="place_add")
     */
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $place = new Place();
        $placeForm = $this->createForm(PlaceType::class, $place);
        $placeForm->handleRequest($request);

        if ($placeForm->isSubmitted() && $placeForm->isValid() )
        {
            $em->persist($place);
            $em->flush();

            return $this->redirectToRoute('create_outing');
        }

        return $this->render('place/add.html.twig', [
            'placeForm' => $placeForm->createView(),
        ]);
    }
}
