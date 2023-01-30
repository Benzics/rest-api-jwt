<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/api/users/register", name="user_register")
     */
    public function register(Request $request, ValidatorInterface $validator, UserPasswordEncoderInterface $encoder): JsonResponse
    {
        $user = new User();
        return $this->json([
            'pass' => $encoder->encodePassword($user, '123456')
        ]);
        // return $this->json([
        //     'message' => 'Welcome to your new controller!',
        //     'path' => 'src/Controller/UserController.php',
        // ]);
    }
}
