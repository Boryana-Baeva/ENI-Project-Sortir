<?php

namespace App\Controller;

use App\Data\SearchData;
use App\Entity\Outing;
use App\Entity\Place;
use App\Entity\State;
use App\Entity\User;
use App\Form\CancelType;
use App\Form\OutingType;
use App\Form\PlaceType;
use App\Form\SearchType;
use App\Repository\CampusRepository;
use App\Repository\OutingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


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

            $stateCreated = $this->defineState(State::CREATED, $em);

            $outing->setState($stateCreated);

            $em->persist($outing);
            $em->flush();

            $this->addFlash('success', "La sortie a été crée !");
            return $this->redirectToRoute('outing_search');
        }

        return $this->render('outing/create.html.twig', [
            'outingForm' => $outingForm->createView(),
            'placeForm' => $placeForm->createView(),
        ]);
    }

    /**
     * @Route ("/outing/publish/{id}", name="outing_publish")
     */
    public function publish($id, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $outingRepo = $em->getRepository(Outing::class);
        $outing = $outingRepo->find($id);

        if ($this->getUser() != $outing->getOrganizer())
        {
            $this->addFlash('failure', "Vous ne pouvez pas publier des sortie dont vous n'êtes pas l'otganisateur !");
            return $this->redirectToRoute('outing_search');
        }

        $stateOpen = $this->defineState(State::OPEN, $em);

        $outing->setState($stateOpen);
        $em->persist($outing);
        $em->flush();

        $this->addFlash('success', "La sortie a été publiée !");
        return $this->redirectToRoute('outing_search');

    }

    /**
     * @Route("/outing/list", name="outing_list")
     */
    public function list(EntityManagerInterface $em)
    {
        $outingRepo = $em->getRepository(Outing::class);
        $listOutings = $outingRepo->findAll();

        $this->checkState($listOutings, $em);


        return $this->render('outing/list.html.twig', [
            'outingList' => $listOutings
        ]);
    }

    /**
     * @Route("/", name="outing_search")
     */
    public function search(EntityManagerInterface $em, Request $request)
    {
        $outingRepo = $em->getRepository(Outing::class);
        $outingList = $outingRepo->findBy([], ["startDateTime" => "DESC"], 30);
        $this->checkState($outingList, $em);

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

            $outingList = $outingRepo->findSearched($data, $searchParams);

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
     * @Route ("/outing/modify/{id}",  name="outing_modify")
     */
    public function modify($id, EntityManagerInterface $em, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $outingRepo = $em->getRepository(Outing::class);
        $outing = $outingRepo->find($id);
        if ($outing->getOrganizer() != $this->getUser())
        {
            throw new AccessDeniedException('Impossible de modifier des sorties  si vous  n\'êtes pas l\'organisateur');
        }

        $place = new Place();
        $place->setAddDate(new \DateTime());
        $placeForm = $this->createForm(PlaceType::class, $place);

        $placeForm->handleRequest($request);
        $modifyForm = $this->createForm(OutingType::class, $outing);

        $modifyForm->handleRequest($request);

        if ($placeForm->isSubmitted() && $placeForm->isValid() )
        {
            $em->persist($place);
            $em->flush();
        }

        if ($modifyForm->isSubmitted() && $modifyForm->isValid())
        {
            $em->persist($outing);
            $em->flush();

            $this->addFlash('success', "La sortie a été modifiée !");
           return $this->redirectToRoute('outing_details', [
                'id'=>$outing->getId()
            ]);
        }

        return $this->render('outing/modify.html.twig', [
            'outing'=>  $outing,
            'modifyForm'=> $modifyForm->createView(),
            'placeForm'=> $placeForm->createView()
        ]);
    }

    /**
     * @Route("/outing/subscribe/{id}", name="outing_subscribe",requirements={"id": "\d+"},
     *     methods={"GET"})
     */
    public function subscribe($id, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $outingRepo = $em->getRepository(Outing::class);
        $outing = $outingRepo->find($id);

        $deadline = $outing ->getEntryDeadline();
        $today = new \DateTime();

        if ($deadline <= $today)
        {
            $this->addFlash('failure', 'Vous ne pouvez pas vous inscrire à une sortie après la date de clôture des inscriptions');
            return $this->redirectToRoute('outing_search', []);
        }

        $limitSubs =  $outing -> getMaxNumberEntries();
        $nbrParticipants = $outing->getParticipants()->count();

        $userRepo = $em->getRepository(User::class);
        $user = $userRepo->findOneBy(["username" => $this->getUser()->getUsername()]);

        if ($outing->getParticipants()->contains($user))
        {
            $this->addFlash('failure',"Vous êtes déjà inscrit(e) à cette sortie(".$outing->getName().")." );
            return $this->redirectToRoute('outing_search', []);
        }
        elseif ( $limitSubs == $outing->getParticipants()->count())
        {
            $this->addFlash('failure', "Nombre de participants max atteint pour cette sortie (". $outing->getName() .").");
            return $this->redirectToRoute('outing_search', []);
        }
        elseif ($outing->getState()->getLabel() == 'closed' )
        {
            $this->addFlash('failure',"Inscription à cette sortie (". $outing->getName() .") clôturée !.");
            return $this->redirectToRoute('outing_search', []);
        }

        if ($nbrParticipants < $limitSubs && $deadline >= $today && $outing->getState()->getLabel() == State::OPEN)
        {
            $outing->addParticipant($user);
            $user->addOutingSubscribed($outing);

            if ($nbrParticipants == $limitSubs)
            {
                $stateClosed = $this->defineState(State::CLOSED, $em);
                $outing->setState($stateClosed);
            }

            $em->persist($outing);
            $em->flush();

            $this->addFlash("successDesInscription", "Vous êtes bien inscrit(e) à la sortie \" "
                                . $outing->getName() . "\" !"   );

            return $this->redirectToRoute('outing_details', [
                'id'=>$id
            ]);
        }
        $this->addFlash('failure',"Not working");
        return $this->redirectToRoute('outing_search', []);

    }

    /**
     * @Route("/outing/unsubscribe/{id}", name="outing_unsubscribe",requirements={"id": "\d+"},
     *     methods={"GET"})
     */
    public function unsubscribe($id, EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $outingRepo = $em->getRepository(Outing::class);
        $outing = $outingRepo->find($id);

        $limitSubs =  $outing -> getMaxNumberEntries();
        $nbrParticipants = $outing->getParticipants()->count();
        $startDate = $outing->getStartDateTime();
        $deadline = $outing->getEntryDeadline();
        $today = new \DateTime();

        $userRepo = $em->getRepository(User::class);
        $user = $userRepo->findOneBy(["username" => $this->getUser()->getUsername()]);

        if (!$outing->getParticipants()->contains($user))
        {
            $this->addFlash('failure',"Vous n'êtes pas inscrit(e) à cette sortie(".$outing->getName().").");
            return $this->redirectToRoute('outing_search', []);
        }


        if ($today < $deadline)
        {
            $outing->removeParticipant($user);
            $user->removeOutingSubscribed($outing);

            if ($nbrParticipants < $limitSubs)
            {
                $stateOpen = $this->defineState(State::OPEN, $em);
                $outing->setState($stateOpen);
            }

            $em->persist($outing);
            $em->persist($user);
            $em->flush();

            $this->addFlash("successDesInscription", "Vous êtes bien désinscrit(e) de la sortie \" " . $outing->getName() . "\" !"   );

            return $this->redirectToRoute('outing_details', [
                'id'=>$id
            ]);
        }
        else {
            $this->addFlash('failure', 'Vous ne pouvez pas vous désister d\'une sortie après la date de clôture !');
        }

        return $this->redirectToRoute('outing_search', []);

    }

    /**
     * @Route("/outing/cancel/{id}", name="outing_cancel")
     */
    public function cancel($id, EntityManagerInterface $em, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $today = new \DateTime();

        $outingRepo = $em->getRepository(Outing::class);

        $outing = $outingRepo->find($id);

        $cancelForm = $this->createForm(CancelType::class, $outing);

        $cancelForm->handleRequest($request);

        if ($cancelForm->isSubmitted() && $cancelForm->isValid() )
        {
            if($outing->getOrganizer()==$this->getUser() && $outing->getStartDateTime() > $today)
            {
                $stateCanceled = $this->defineState(State::CANCELED, $em);
                $outing->setState($stateCanceled);

                $em->persist($outing);
                $em->flush();

                $this->addFlash('success', 'Outing was canceled with success');

                return $this->redirectToRoute('outing_details', [
                    'id'=>$id
                ]);
            }

        }

        return $this->render('outing/cancel.html.twig', [
            'cancelForm'=>  $cancelForm->createView(),
            'outing'=>$outing
        ]);
    }


    public function defineState($stateLabel, EntityManagerInterface $em)
    {
        $stateRepo = $em->getRepository(State::class);

        if ($stateRepo->findOneBy(['label'=>$stateLabel]) == null)
        {
            $state = new State();
            $state->setLabel($stateLabel);
            $em->persist($state);
            $em->flush();
        }
        else
        {
            $state = $stateRepo->findOneBy(['label'=>$stateLabel]);
        }

        return $state;
    }

    public function checkState($listOutings, EntityManagerInterface $em)
    {
        foreach ($listOutings as $outing)
        {
            $today = new \DateTime();
            $minutes = $outing->getDuration();
            $deadline = $outing->getEntryDeadline();

            $startDateTime = $outing->getStartDateTime();
            $startDateTimeString = $startDateTime->format('m/d/Y H:i');
            $timestamp = strtotime("+{$minutes} minutes",strtotime($startDateTimeString));

            $endDateTime = new \DateTime();
            $endDateTime->setTimestamp($timestamp);
            $endDateTimeString = $endDateTime->format('m/d/Y H:i');
            $timestampMonthAfter = strtotime("+1 month",strtotime($endDateTimeString));

            $monthAfter = new \DateTime();
            $monthAfter->setTimestamp($timestampMonthAfter);


            if ($outing->getState()->getLabel() != State::CREATED || $outing->getState()->getLabel() != State::CANCELED)
            {
                $stateLabel = State::OPEN;
                if ($deadline < $today && $startDateTime > $today)
                {
                    $stateLabel = State::CLOSED;

                }
                elseif($outing->getParticipants()->count() >= $outing->getMaxNumberEntries())
                {
                    $stateLabel = State::CLOSED;
                }
                elseif($outing->getParticipants()->count() < $outing->getMaxNumberEntries())
                {
                    $stateLabel = State::OPEN;
                }
                elseif ($startDateTime <= $today && $endDateTime >= $today)
                {
                    $stateLabel = State::IN_PROGRESS;

                }
                elseif ($startDateTime < $today && $today <= $monthAfter)
                {
                    $stateLabel = State::PAST;

                }
                elseif ($today > $monthAfter)
                {
                    $stateLabel = State::ARCHIVED;
                }

                if ($outing->getState()->getLabel() != $stateLabel)
                {
                    $state = $this->defineState($stateLabel, $em);
                    $outing->setState($state);
                    $em->persist($outing);
                    $em->flush();
                }
            }
        }
    }

}
