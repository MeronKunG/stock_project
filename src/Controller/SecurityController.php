<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\WarehouseInfo;
use App\Form\UserFormType;
use App\Form\UpdateUserType;
use App\Security\LoginFormAuthenticator;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Component\HttpFoundation\Request;

use App\Repository\UserRepository;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, UserPasswordEncoderInterface $passwordEncoder): Response
    {
            // get the login error if there is one
            $error = $authenticationUtils->getLastAuthenticationError();
            // last username entered by the user
            $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * @Route("/user/adduser", name="app_add_user")
     */
    public function addUser(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $authenticator,
        ValidatorInterface $validator,
        AuthorizationCheckerInterface $authChecker
    ): Response {
        if (false === $authChecker->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Unable to access this page!');
        }
        $user = new User();
        $errors = $validator->validate($user);
        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (count($errors) > 0) {
                $errorsString = (string)$errors;
                echo $errorsString;
            }
            $user->setUserStatus(1);
            $user->setDefaultWarehouse($form->get('defaultWarehouse')->getData());
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            if($form->get('roles')->getData() == 'ROLE_ADMIN') {
                $user->setRoles(array("ROLE_USER","ROLE_SUPER_USER","ROLE_ADMIN"));
            } else if($form->get('roles')->getData() == 'ROLE_SUPER_USER') {
                $user->setRoles(array("ROLE_USER","ROLE_SUPER_USER"));
            } else {
                $user->setRoles(array("ROLE_USER"));
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'เพิ่มข้อมูลเรียบร้อยแล้ว');
            return $this->redirect($this->generateUrl('app_add_user'));
            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }
        return $this->render('security/adduser.html.twig', [
            'userForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/user/dashboard", name="user.dashboard")
     */
    public function dashboardAction(Request $request, UserRepository $userRepository, PaginatorInterface $paginator)
    {
        date_default_timezone_set("Asia/Bangkok");
//        $users = $userRepository->allUserInfo();
        $users = $userRepository->allUserInfo();
        $result = $paginator->paginate(
            $users,
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        if ($request->query->get('search')) {
            $userResult = $userRepository->findByUsername($request->query->get('search'));
            $result = $paginator->paginate(
                $userResult,
                $request->query->getInt('page', 1)/*page number*/,
                10/*limit per page*/
            );
        }
        return $this->render('security/userDashboard.html.twig', [
            'users' => $result
        ]);
    }

    /**
     * @Route("/user/edituser/{id}", name="user.edit")
     * @paramConverter("user", class="App\Entity\User")
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editUserAction(Request $request, User $user, UserPasswordEncoderInterface $passwordEncoder)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(UpdateUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('password')->getData() != null) {
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
            }
            $user->setDefaultWarehouse($form->get('defaultWarehouse')->getData());
            $user->setRoles(array("0" => $form->get('roles')->getData()));
            $entityManager->flush();
            $this->addFlash('success', 'แก้ไขข้อมูลเรียบร้อยแล้ว');
            return $this->redirect($this->generateUrl('user.dashboard'));
        }
        return $this->render('security/editUser.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function userLogout() {
        return "";
    }
}
