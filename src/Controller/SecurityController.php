<?php

namespace App\Controller;

use App\Form\RegistrationType;
use App\Security\BillingAuthenticator;
use App\Security\User;
use App\Service\BillingClient;
use App\Service\BillingTransaction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private BillingClient $billingClient;
    private BillingTransaction $billingTransaction;

    public function __construct(BillingClient $billingClient, BillingTransaction $billingTransaction)
    {
        $this->billingClient = $billingClient;
        $this->billingTransaction = $billingTransaction;
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, AuthorizationCheckerInterface $authChecker): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute('course_index');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/profile", name="app_profile")
     */
    public function profile()
    {
        $user = $this->getUser();

        $balance = $this->billingClient->getBalance($user);
        $transactions = $this->billingTransaction->getTransactions($user->getApiToken());

        return $this->render('security/profile.html.twig', [
            'user' => $user,
            'balance' => $balance,
            'transactions' => $transactions,
        ]);
    }

    /**
     * @Route("/registration", name="registration")
     */
    public function register(Request $request,  UserAuthenticatorInterface $authenticator,
                             BillingAuthenticator $formAuthenticator): Response
    {
        if($this->getUser()){
            return $this->redirectToRoute('app_profile');
        }

        $error = null;

        $form = $this->createForm(RegistrationType::class, null);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $email = $formData['email'];
            $password = $formData['password'];
            $credentials = [
                'email' => $email,
                'password' => $password,
            ];
            $response = $this->billingClient->register($credentials);

            if ($response instanceof User) {
                return $authenticator->authenticateUser(
                    $response,
                    $formAuthenticator,
                    $request);
            }
                $error = $response;
        }

        return $this->render('registration/index.html.twig', [
            'registrationForm' => $form->createView(),
            'error' => $error,
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
