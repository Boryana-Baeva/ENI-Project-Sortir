<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Outing;
use App\Entity\Place;
use App\Entity\State;
use App\Form\OutingType;
use App\Form\PlaceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OutingController extends AbstractController
{
    /**
     * @Route ("/outing/create", name="create_outing")
     */
    public function createOuting(Request $request, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $outing = new Outing();
        $place = new Place();


        $outingForm = $this->createForm(OutingType::class, $outing);
        $placeForm = $this->createForm(PlaceType::class, $place);

        $outingForm->handleRequest($request);
        $placeForm->handleRequest($request);


        if($outingForm->isSubmitted() && $outingForm->isValid())
        {
            if ($placeForm->isSubmitted() && $placeForm->isValid() )
            {
                $em->persist($place);
                $em->flush();
            }
           /* $namePlace = $outingForm->get('place')->getData();
            $street = $outingForm->get('street')->getData();
            $latitude = $outingForm->get('latitude')->getData();
            $longitude = $outingForm->get('longitude')->getData();
            $nameCity = $outingForm->get('city')->getData();

            $place = new Place();
            $place->setName($namePlace);
            $place->setStreet($street);
            $place->setLatitude($latitude);
            $place->setLongitude($longitude);

            $cityRepo = $em->getRepository(City::class);
            $city = $cityRepo->findOneBy(['name'=>$nameCity]);

            $place->setCity($city);

            $outing->setPlace($place)*/;
            $outing->setCampus($this->getUser()->getCampus());
            $outing->setOrganizer($this->getUser());

            $stateRepo = $em->getRepository(State::class);
            $stateCreated= $stateRepo->findOneBy(['label'=>'created']);
            $outing->setState($stateCreated);
            dump($outing);
            $em->persist($outing);
            $em->flush();
            //todo:rediriger vers la liste des sorties
            return $this->redirectToRoute('home');

        }

        return $this->render('outing/create.html.twig', [
            'outingForm' => $outingForm->createView(),
            'placeForm' => $placeForm->createView(),
        ]);


    }
}
