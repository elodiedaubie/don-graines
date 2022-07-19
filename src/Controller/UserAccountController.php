<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Donation;
use App\Form\EditUserFormType;
use App\Repository\DonationRepository;
use App\Repository\SeedBatchRepository;
use App\Service\DonationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/mon-compte', name: 'user_account')]
#[IsGranted('ROLE_USER')]
class UserAccountController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private DonationManager $donationManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        DonationManager $donationManager
    ) {
        $this->entityManager = $entityManager;
        $this->donationManager = $donationManager;
    }

    #[Route('/', name: '')]
    public function index(): Response
    {
        if ($this->getUSer() && $this->getUSer() instanceof User) {
            $user = $this->getUser();
        }

        return $this->render('user_account/index.html.twig', [
            'user' => $user,
            'availableBatches' =>  $user->getAvailableBatches(),
            'requestedDonations' => $user->getDonationsReceived(),
            'donations' => $user->getDonationsMade(),
            'favoriteList' =>  $user->getFavoriteList()
        ]);
    }

    #[Route('/modifier', name: '_edit')]
    public function edit(Request $request): Response
    {

        //check if there is an instance of User
        if ($this->getUser() && $this->getUser() instanceof User) {
            $user = $this->getUser();
        }

        $form = $this->createForm(EditUserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setUserName($form->get('username')->getData());
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $this->addFlash('success', 'Votre profil a bien été modifié');
            return $this->redirectToRoute('user_account');
        }

        return $this->render('user_account/edit_user.html.twig', [
            "editUserForm" => $form->createView()
        ]);
    }

    #[Route('/supprimer', name: '_delete')]
    public function delete(
        Request $request,
        TokenStorageInterface $tokenStorage
    ): Response {

        if ($this->getUser() instanceof User) {
            $user = $this->getUser();
        }

        //check if user has seedBatches
        if ($user->getSeedBatches() !== null) {
            foreach ($user->getSeedBatches() as $seedBatch) {
                if ($seedBatch->hasDonationInProgress()) {
                    //there is at least on donation in progress, user has to terminate it to delete the account
                    $this->addFlash(
                        'danger',
                        'Vous avez un lot de graine avec une donation en cours,
                         vous ne pouvez pas supprimer votre compte'
                    );
                    return $this->redirectToRoute('user_account');
                }
                //there is no donations in progress, delete others
                $this->donationManager->deleteDonations($seedBatch);
            }
        }

        if ($user->getDonationsReceived() !== null) {
            if ($this->donationManager->hasDonationInProgress($user->getDonationsReceived())) {
                //there is at least on donation in progress, user has to terminate it to delete the account
                $this->addFlash(
                    'danger',
                    'Vous avez un lot de graine avec une donation en cours, vous ne pouvez pas supprimer votre compte'
                );
                return $this->redirectToRoute('user_account');
            }
            //there is no donations  in progress, remove them all
            foreach ($user->getDonationsReceived() as $donation) {
                $this->entityManager->remove($donation);
            }
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();
        $request->getSession()->invalidate();
        $tokenStorage->setToken();
        $this->addFlash('success', 'votre compte a bien été supprimé');
        return $this->redirectToRoute('home');
    }

    #[Route('/mes-demandes', name: '_requests')]
    public function showRequests(): Response
    {
        //check if there is an instance of User
        if ($this->getUser() && $this->getUser() instanceof User) {
            $user = $this->getUser();
        }

        return $this->render('user_account/show_requests.html.twig', [
            'requestedDonations' => $user->getDonationsReceived(),
        ]);
    }

    #[Route('/mes-dons', name: '_donations')]
    public function showDonations(): Response
    {
        //check if there is an instance of User
        if ($this->getUser() && $this->getUser() instanceof User) {
            $user = $this->getUser();
        }

        return $this->render('user_account/show_donations.html.twig', [
            'donations' => $user->getDonationsMade()
        ]);
    }

    #[Route('/mon-stock', name: '_available')]
    public function showAvailableBatches(): Response
    {
        //check if there is an instance of User
        if ($this->getUser() && $this->getUser() instanceof User) {
            $user = $this->getUser();
        }

        return $this->render('user_account/show_available_batches.html.twig', [
            'availableBatches' =>  $user->getAvailableBatches(),
        ]);
    }

    #[Route('/mes-favoris', name: '_favorite')]
    public function showFavoriteList(): Response
    {
        //check if there is an instance of User
        if ($this->getUser() && $this->getUser() instanceof User) {
            $user = $this->getUser();
        }

        return $this->render('user_account/show_favorite_list.html.twig', [
            'favoriteList' =>  $user->getFavoriteList(),
        ]);
    }

    #[Route('/don/{id}', name: '_donation', requirements: ['id' => '\d+'])]
    public function showDonation(Donation $donation): Response
    {
        return $this->render('user_account/show_donation.html.twig', [
            'donation' => $donation
        ]);
    }
}
