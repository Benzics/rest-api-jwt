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
use App\Services\AmazonS3Service;
use Psr\Container\ContainerInterface;
use App\Entity\Photo;

class UserController extends AbstractController
{
    private $userRepository;
    public $container;

    public function __construct(UserRepository $userRepository, ContainerInterface $container)
    {
        $this->userRepository = $userRepository;
        $this->container = $container;
    }

    /**
     * @Route("/api/users/register", name="user_register", methods={"POST", "GET"})
     */
    public function register(
        Request $request,
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $encoder,
        EntityManagerInterface $entityManager,
        AmazonS3Service $amazonS3Service
    ): JsonResponse
    {
        $email = (string) $request->get('email');
        $firstName = (string) $request->get('firstName');
        $lastName = (string) $request->get('lastName');
        $password = (string) $request->get('password');

        // we need to make sure a user with this email does not already exist
        $checkUser = $this->userRepository->findBy(['email' => $email]);

        if($checkUser) {
            // this user exists so we return message with a response code of 400
            return $this->json(['message' => 'The email address already exists.'], 400);
        }

        $user = new User();

        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        $user->setPassword($password);

        // we set our default avatar
        $user->setAvatar('public/images/avatar.jpg');

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

        $userId = $user->getId();

        // we check if this request has files then we upload to Amazon S3
        if($request->files->get('photos')) {

            // we are expecting our photos to be an array of photos
            foreach($request->files->get('photos') as $image) {

                $currentName = md5(uniqid()) . '.' . $image->guessExtension();
                $photoUrl = $amazonS3Service->uploadFile($image, $currentName);

                // after uploading image to s3 bucket, we save the file name in our database
                $photo = new Photo();
                $photo->setName($currentName);
                $photo->setUrl($photoUrl);
                $photo->setUser($user);

                $entityManager->persist($photo);

            }
            
        }
        $entityManager->flush();

        $data = ['message' => 'User created successfully.', 'userId' => $userId];
        return $this->json($data);
    }

    /**
     * @Route("/api/users/me", name="user_details", methods={"GET"})
     */
    public function details(): JsonResponse
    {
        $user = $this->getUser();
       
        $photosArray = [];

        // we want to return all user photos as array we can easily interact with
        foreach($user->getPhotos() as $row) {
            $photosArray[] = [
                'id' => $row->getId(),
                'name' => $row->getName(),
                'url' => $row->getUrl(),
                'user' => $row->getUser(),
            ];
        }

        return $this->json([
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'fullName' => $user->getFullName(),
            'email' => $user->getEmail(),
            'active' => $user->getActive(),
            'avatar' => $user->getAvatar(),
            'photos' => $photosArray,
            'createdAt' => $user->getCreatedAt(),
            'updatedAt' => $user->getUpdatedAt(),
        ]);
    }
}
