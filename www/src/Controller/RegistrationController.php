<?php
namespace App\Controller;
use App\Entity\User;
use App\Entity\Plan;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('homepage');
        }
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $plainPassword = $request->request->get('password');
            $firstname = $request->request->get('firstname');
            $lastname = $request->request->get('lastname');

            if ($email && $plainPassword && $firstname && $lastname) {
                $user = new User();
                $user->setEmail($email);
                $user->setFirstname($firstname);
                $user->setLastname($lastname);
                $user->setDob(new \DateTime('1990-01-01'));
                $user->setRoles(['ROLE_USER']);
                
                // Hash the password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $plainPassword
                    )
                );
                // Assign default plan (FREE)
                $defaultPlan = $entityManager->getRepository(Plan::class)->findOneBy(['name' => 'FREE']);
                if (!$defaultPlan) {
                    // Fallback to ID 1 if name not found
                    $defaultPlan = $entityManager->getRepository(Plan::class)->find(1);
                }
                if ($defaultPlan) {
                    $user->setPlan($defaultPlan);
                }
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Compte créé avec succès ! Veuillez vous connecter.');
                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash('danger', 'Veuillez remplir tous les champs obligatoires (e-mail, mot de passe, prénom, nom).');
            }
        }
        return $this->render('registration/register.html.twig');
    }
}