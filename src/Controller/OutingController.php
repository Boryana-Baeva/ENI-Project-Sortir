<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Entity\City;
use App\Entity\Outing;
use App\Entity\Place;
use App\Entity\State;
use App\Form\OutingType;
use App\Form\PlaceType;
use App\Form\SearchType;
use App\Repository\OutingRepository;
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
        $outing->setCampus($this->getUser()->getCampus());
        $place = new Place();
        $place->setAddDate(new \DateTime());
        $outingForm = $this->createForm(OutingType::class, $outing);
        $placeForm = $this->createForm(PlaceType::class, $place);

        $placeForm->handleRequest($request);
        $outingForm->handleRequest($request);

        if ($placeForm->isSubmitted() && $placeForm->isValid() )
        {
            $em->persist($place);
            $em->flush();
        }
        else if($outingForm->isSubmitted() && $outingForm->isValid())
        {
            $outing->setOrganizer($this->getUser());

            $stateRepo = $em->getRepository(State::class);
            $stateCreated= $stateRepo->findOneBy(['label'=>'created']);
            $outing->setState($stateCreated);

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

    /**
     * @Route("/outing/list", name="outing_list")
     */
    public function list(EntityManagerInterface $em)
    {
        $outingRepo = $em->getRepository(Outing::class);
        $listOutings = $outingRepo->findAll();

        return $this->render('outing/list.html.twig', [
            'listOutings' => $listOutings
        ]);
    }

    /**
     * @Route("/outing/search", name="outing_search")
     */
    public function search(OutingRepository $repository, Request $request)
    {
        $outingList = $repository->findBy([], ["startDateTime" => "DESC"], 30);
        $data = new SearchData();
        $searchForm = $this->createForm(SearchType::class, $data);

        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted())
        {
            $outingList = $repository->findSearched($data, $this->getUser());
        }

        return $this->render('outing/search.html.twig', [
            'outingList' => $outingList,
            'searchForm' => $searchForm->createView()
        ]);
    }
}
