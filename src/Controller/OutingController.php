<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Entity\Outing;
use App\Entity\Place;
use App\Entity\State;
use App\Entity\User;
use App\Form\OutingType;
use App\Form\PlaceType;
use App\Form\SearchType;
use App\Repository\CampusRepository;
use App\Repository\OutingRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class OutingController extends AbstractController
{
    /**
     * @Route ("/outing/create", name="outing_create")
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
            return $this->redirectToRoute('outing_search');
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
     * @Route("/", name="outing_search")
     */
    public function search(OutingRepository $repository, Request $request, CampusRepository $campusRepository)
    {
        $outingList = $repository->findBy([], ["startDateTime" => "DESC"], 30);

        $data = new SearchData();
        $searchForm = $this->createForm(SearchType::class, $data);

        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted())
        {

            $searchParams = [
                'connectedUser' => $this->getUser(),
                'campus' => $request->query->get('campus'),
                'outingName' => $request->query->get('q'),
                'minDate' => $request->query->get('minDate'),
                'maxDate' => $request->query->get('maxDate')
                ];

            $outingList = $repository->findSearched($data, $searchParams);

        }

        return $this->render('outing/search.html.twig', [
            'outingList' => $outingList,
            'searchForm' => $searchForm->createView()
        ]);
    }

    /**
     * @Route("/outing/{id}", name="outing_details")
     */
    public function details($id, EntityManagerInterface $em)
    {
        $outingRepo = $em->getRepository(Outing::class);
        $outing = $outingRepo->find($id);

        return $this->render('outing/details.html.twig', [
            'outing' => $outing
        ]);
    }

    /**
     * @Route("/outing/subscribe/{id}", name="outing_subscribe")
     */
    public function subscribe($id, EntityManagerInterface $em)
    {
        $outingRepo = $em->getRepository(Outing::class);
        $outing = $outingRepo->find($id);
        $user = $this->getUser();

        $participants = $outing->getParticipants();
        $participants->add($user);

        $outings = $user->getOutingsSubscribed();
        $outings->add($outing);

        $em->persist($outing);
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('outing_details');

    }
}
