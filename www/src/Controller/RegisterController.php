<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\DependencyInjection\Loader\Configurator\validator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher, 
        EntityManagerInterface $entityManager, 
        ValidatorInterface $validator) : JsonResponse
    {
        //! Récupération des datas passe depuis le front 
        $data = json_decode($request->getContent(), true);

        //! validation des donnees 
        if (!isset($data['email']) || !isset($data['password']) || !isset($data['firstname']) || !isset($data['lastname'])) {
           return $this->json([
                'success' => 'false',
                'message' => 'Tous les champs sont obligatoires.'
           ], Response::HTTP_BAD_REQUEST);
        }
        //! on verifie si l'email existe deja
        $existingEmail = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingEmail) {
            return $this->json([
                'success' => 'false',
                'message' => 'Cet email est déjà utilisé.'
            ], Response::HTTP_CONFLICT);
        }

        //! Tout es ok , on peut créer le nouvel utilisateur
        $user = new User();
        $user->setEmail($data['email']);
        $user->setFirstname($data['firstname']);
        $user->setLastname($data['lastname']);
        $user->setCreatedAt(new \DateTime());
        $user->setIsActive(true);
        //! on definit le role (par defaut ROLE_USER)
        if(isset($data['roles']) && is_array($data['roles']) )
        {
            $user->setRoles($data['roles']);
        } else {
            $user->setRoles(['ROLE_USER']);
        }
        //! on hash le mot de passe
        $hashedPassword = $passwordHasher->hashPassword(
           $user, 
           $data['password']
        );
        $user->setPassword($hashedPassword);

        //! on valide l'entité User
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json([
                'success' => 'false',
                'message' => $errorMessages,
            ], Response::HTTP_BAD_REQUEST);
        }

        //! on peut sauvegarder en bdd
         $entityManager->persist($user);
         $entityManager->flush();

         //! on retourne une reponse json
            return $this->json([
                'success' => 'true',
                'message' => 'Inscription réussie. Vous pouvez maintenant vous connecter.',
                'user' => [
                    "id" => $user->getId(),
                    "email" => $user->getEmail(),
                    "firstname" => $user->getFirstname(),
                    "lastname" => $user->getLastname(),
                    "roles" => $user->getRoles(),
                    "createdAt" => $user->getCreatedAt()->format('Y-m-d H:i:s'),
                ],
            ], Response::HTTP_CREATED);
    }
}
