<?php

namespace App\Controller;

use App\Entity\UserContact;
use App\Form\UserContactType;
use App\Repository\UserContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

#[IsGranted('ROLE_USER')]
#[Route('/contacts')]
class ContactController extends AbstractController
{
    #[Route('', name: 'app_contact_index', methods: ['GET'])]
    public function index(UserContactRepository $contactRepository, UserRepository $userRepository): Response
    {
        $currentUser = $this->getUser();
        $contacts = $contactRepository->findBy(['user' => $currentUser]);
        $contactEmails = array_map(fn($c) => $c->getEmail(), $contacts);
        
        $allUsers = $userRepository->findAll();
        $suggestions = [];
        foreach ($allUsers as $user) {
            if ($user !== $currentUser && !in_array($user->getEmail(), $contactEmails)) {
                $suggestions[] = $user;
            }
        }

        return $this->render('contact/index.html.twig', [
            'contacts' => $contacts,
            'suggestions' => $suggestions,
        ]);
    }

    #[Route('/new', name: 'app_contact_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $contact = new UserContact();
        $contact->setUser($this->getUser());
        $form = $this->createForm(UserContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contact);
            $entityManager->flush();

            $this->addFlash('success', 'Contact créé avec succès.');

            return $this->redirectToRoute('app_contact_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contact/form.html.twig', [
            'contact' => $contact,
            'form' => $form->createView(),
            'title' => 'New Contact'
        ]);
    }

    #[Route('/{id}/edit', name: 'app_contact_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, UserContact $contact, EntityManagerInterface $entityManager): Response
    {
        // Ensure the contact belongs to the current user
        if ($contact->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(UserContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Contact mis à jour avec succès.');

            return $this->redirectToRoute('app_contact_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('contact/form.html.twig', [
            'contact' => $contact,
            'form' => $form->createView(),
            'title' => 'Edit Contact'
        ]);
    }

    #[Route('/{id}', name: 'app_contact_delete', methods: ['POST'])]
    public function delete(Request $request, UserContact $contact, EntityManagerInterface $entityManager): Response
    {
        if ($contact->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$contact->getId(), $request->request->get('_token'))) {
            $entityManager->remove($contact);
            $entityManager->flush();
            $this->addFlash('success', 'Contact supprimé avec succès.');
        }

        return $this->redirectToRoute('app_contact_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/ajax-add', name: 'app_contact_add_ajax', methods: ['POST'], priority: 10)]
    public function addAjax(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userId = $data['user_id'] ?? null;
        
        if (!$userId) {
            return new JsonResponse(['error' => 'No user id provided'], 400);
        }

        $targetUser = $userRepository->find($userId);
        if (!$targetUser) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $contact = new UserContact();
        $contact->setUser($this->getUser());
        $contact->setFirstname($targetUser->getFirstname() ?? 'Utilisateur');
        $contact->setLastname($targetUser->getLastname() ?? '');
        $contact->setEmail($targetUser->getEmail());

        $entityManager->persist($contact);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}
