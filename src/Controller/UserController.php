<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\ORM\EntityManagerInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/api/users/register", name="user_register")
     */
    public function register(
        Request $request,
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $encoder,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $email = (string) $request->get('email');
        $firstName = (string) $request->get('firstName');
        $lastName = (string) $request->get('lastName');
        $password = (string) $request->get('password');

        // we need to make sure a user with this email does not already exist
        $checkUser = $userRepository->findBy(['email' => $email]);

        if($checkUser) {
            // this user exists so we return message with a response code of 400
            return $this->json(['message' => 'The email address already exists.'], 400);
        }

        $user = new User();

        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        $user->setPassword($password);

        // we check if there are validation errors
        $errors = $validator->validate($user);

        if(count($errors) > 0) {
            // there are validation errors present so we return an error response
            $errorPlain = (string) $errors;

            return $this->json(['message' => $errorPlain], 400);
        }

        // after our plain password passes validation
        // we encrypt our password before saving it
        $encryptedPass = $encoder->encodePassword($user, $password);
        $user->setPassword($encryptedPass);

        // all validations passed so we save this user data
        $entityManager->persist($user);

        $entityManager->flush();

        $data = ['message' => 'User created successfully.', 'userId' => $user->getId()];
        return $this->json($data);
    }
}
