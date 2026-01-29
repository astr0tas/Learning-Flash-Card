<?php

namespace App\Controller;

use App\Config\Constants;
use App\Entity\RecoveryTokenEntity;
use App\Service\EmailService;
use Symfony\Component\Routing\Attribute\Route;
use App\Config\Routes;
use App\Config\TwigTemplate;
use App\Entity\UserEntity;
use App\Utility\Utility;
use Google\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;

class AuthenticationController extends BaseController
{
  // Inject the value directly into the constructor
  public function __construct(
    #[Autowire(env: 'GOOGLE_CLIENT_ID')] private string $googleOAuthClientId,
    private Security $security
  ) {}

  #[Route(path: Routes::LOGIN_ROUTE['URL'], name: Routes::LOGIN_ROUTE['NAME'], methods: ['GET', 'POST'])]
  public function LoginAction(Request $request)
  {
    if ($this->getUser()) {
      return $this->redirectUserToHome();
    }

    if ($request->isMethod(Request::METHOD_GET)) {
      // Display login form
      return $this->render(view: TwigTemplate::PAGES['login']);
    } else {
      // Handle login submission
      $postData = $request->request->all();

      // Flash input back to the session to pre-fill the form
      $this->addFlash('email', $postData['email'] ?? '');
      $this->addFlash('remember_me', isset($postData['remember_me']) ? 'checked' : '');

      // Validate post data
      $fields = [
        'email'    => [new Assert\NotBlank(
          message: $this->translator->trans('validation.email.not_blank')
        ), new Assert\Email(
          message: $this->translator->trans('validation.email.invalid')
        )],
        'password' => [new Assert\NotBlank(
          message: $this->translator->trans('validation.password.not_blank')
        )],
      ];
      $errors = $this->validate($postData, $fields);

      if (count($errors) > 0) {
        $this->addFlash('error', $errors);
        return $this->redirectToRoute(Routes::LOGIN_ROUTE['NAME']);
      }

      $user = $this->entityManager
        ->getRepository(UserEntity::class)
        ->findOneBy(criteria: ['email' => $postData['email']]);

      if (!$user || !$user->comparePassword($postData['password'])) {
        $this->addFlash('error', ['general' => [
          $this->translator->trans('login.incorrect_credentials')
        ]]);
        return $this->redirectToRoute(Routes::LOGIN_ROUTE['NAME']);
      }

      $rememberMeBadge = new RememberMeBadge();

      if (isset($postData['remember_me'])) {
        $rememberMeBadge->enable();
      } else {
        $rememberMeBadge->disable();
      }

      $this->security->login($user, 'form_login', null, [
        $rememberMeBadge,
      ]);

      return $this->redirectUserToHome();
    }
  }

  #[Route(path: Routes::LOGIN_WITH_GOOGLE_ROUTE['URL'], name: Routes::LOGIN_WITH_GOOGLE_ROUTE['NAME'], methods: ['POST'])]
  public function LoginWithGoogleAction(Request $request)
  {
    if ($this->getUser()) {
      return $this->redirectUserToHome();
    }

    // 1. Check Google's CSRF Cookie (Security Best Practice)
    $cookieCsrf = $request->cookies->get('g_csrf_token');
    $postCsrf   = $request->request->get('g_csrf_token');

    if (!$cookieCsrf || !$postCsrf || $cookieCsrf !== $postCsrf) {
      $this->addFlash('error', [
        'general' => [
          'Google CSRF validation failed.'
        ]
      ]);
      return $this->redirectToRoute(Routes::LOGIN_ROUTE['NAME']);
    }

    // 2. Get the JWT Token
    $token = $request->request->get('credential');

    if (!$token) {
      $this->addFlash('error', [
        'general' => [
          'No token received from Google.'
        ]
      ]);
      return $this->redirectToRoute(Routes::LOGIN_ROUTE['NAME']);
    }

    // 3. Verify Token with Google Client Library
    $client = new Client(['client_id' => $this->googleOAuthClientId]);

    try {
      $payload = $client->verifyIdToken($token);
    } catch (\Exception $e) {
      $this->addFlash('error', [
        'general' => [
          'Invalid Google Token.'
        ]
      ]);
      return $this->redirectToRoute(Routes::LOGIN_ROUTE['NAME']);
    }

    if ($payload) {
      $email = $payload['email'];
      $givenName = $payload['given_name'];
      $familyName = $payload['family_name'];

      $user = $this->entityManager
        ->getRepository(UserEntity::class)
        ->findOneBy(criteria: ['email' => $email]);

      if (!$user) {
        // Create new user
        $user = new UserEntity();
        $user->setEmail($email);
        $user->setFirstName($givenName);
        $user->setLastName($familyName);
        $user->setPassword(Constants::GOOGLE_OAUTH_PASSWORD, false); // Random password
        $user->setRoles([Constants::ROLES['user']]);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
      }

      $request->request->set('remember_me', 'on');

      $this->security->login($user, 'form_login', null, [
        (new RememberMeBadge())->enable(),
      ]);

      return $this->redirectUserToHome();
    }

    return $this->redirectToRoute(Routes::LOGIN_ROUTE['NAME']);
  }

  #[Route(path: Routes::LOGOUT_ROUTE['URL'], name: Routes::LOGOUT_ROUTE['NAME'], methods: ['POST'])]
  public function LogoutAction()
  {
    return $this->security->logout();
  }

  #[Route(path: Routes::REGISTER_ROUTE['URL'], name: Routes::REGISTER_ROUTE['NAME'], methods: ['GET', 'POST'])]
  public function RegisterAction(Request $request)
  {
    if ($this->getUser()) {
      return $this->redirectUserToHome();
    }

    if ($request->isMethod(Request::METHOD_GET)) {
      // Display registration form
      return $this->render(view: TwigTemplate::PAGES['register']);
    } else {
      // Handle registration submission
    }
  }

  #[Route(path: Routes::FORGOT_PASSWORD_ROUTE['URL'], name: Routes::FORGOT_PASSWORD_ROUTE['NAME'], methods: ['GET', 'POST'])]
  public function ForgotPasswordAction(Request $request)
  {
    if ($this->getUser()) {
      return $this->redirectUserToHome();
    }

    if ($request->isMethod(Request::METHOD_GET)) {
      // Display forgot password form
      return $this->render(view: TwigTemplate::PAGES['forgot_password']);
    } else {
      // Handle post data
      $postData = $request->request->all();

      // Flash input back to the session to pre-fill the form
      $this->addFlash('email', $postData['email'] ?? '');

      // Validate post data
      $fields = [
        'email'    => [new Assert\NotBlank(
          message: $this->translator->trans('validation.email.not_blank')
        ), new Assert\Email(
          message: $this->translator->trans('validation.email.invalid')
        )],
      ];
      $errors = $this->validate($postData, $fields);

      if (count($errors) > 0) {
        $this->addFlash('error', $errors);
        return $this->redirectToRoute(Routes::FORGOT_PASSWORD_ROUTE['NAME']);
      }

      $user = $this->entityManager
        ->getRepository(UserEntity::class)
        ->findOneBy(criteria: ['email' => $postData['email']]);

      if (!$user) {
        $this->addFlash('error', ['general' => [
          $this->translator->trans('forgot_password.user_not_found')
        ]]);
        return $this->redirectToRoute(Routes::FORGOT_PASSWORD_ROUTE['NAME']);
      }

      if ($user->getPassword() === Constants::GOOGLE_OAUTH_PASSWORD) {
        $this->addFlash('error', ['general' => [
          $this->translator->trans('forgot_password.oauth_user')
        ]]);
        return $this->redirectToRoute(Routes::FORGOT_PASSWORD_ROUTE['NAME']);
      }

      if (!$this->sendRecoveryEmail($user)) {
        $this->addFlash('error', ['general' => [
          $this->translator->trans('forgot_password.system_error')
        ]]);
        return $this->redirectToRoute(Routes::FORGOT_PASSWORD_ROUTE['NAME']);
      }

      $this->addFlash(Constants::SESSION['recovery_email_sent_flash_guard'], 1);
      return $this->redirectToRoute(Routes::RECOVERY_EMAIL_SENT_ROUTE['NAME']);
    }
  }

  #[Route(path: Routes::RECOVERY_EMAIL_SENT_ROUTE['URL'], name: Routes::RECOVERY_EMAIL_SENT_ROUTE['NAME'], methods: ['GET'])]
  public function RecoveryEmailSentAction()
  {
    $flashBag = $this->session->getFlashBag();

    if (!$flashBag->get(Constants::SESSION['recovery_email_sent_flash_guard'])) {
      throw $this->createNotFoundException($this->translator->trans('navigation_error.error_404'));
    }
    return $this->render(view: TwigTemplate::PAGES['recovery_email_sent']);
  }

  #[Route(path: Routes::RESET_PASSWORD_ROUTE['URL'], name: Routes::RESET_PASSWORD_ROUTE['NAME'], methods: ['GET', 'POST'])]
  public function ResetPasswordAction(Request $request)
  {
    if ($this->getUser()) {
      return $this->redirectUserToHome();
    }

    if ($request->isMethod(Request::METHOD_GET)) {
      // Display reset password form
      return $this->render(view: TwigTemplate::PAGES['reset_password']);
    } else {
      // Handle reset password submission
    }
  }

  #[Route(path: Routes::EMAIL_VERIFICATION_ROUTE['URL'], name: Routes::EMAIL_VERIFICATION_ROUTE['NAME'], methods: ['GET'])]
  public function EmailVerificationAction(Request $request)
  {
    if ($this->getUser()) {
      return $this->redirectUserToHome();
    }
  }

  private function redirectUserToHome()
  {
    $user = $this->getUser();

    if ($this->isGranted(Constants::ROLES['admin'], $user)) {
      return $this->redirectToRoute(Routes::ADMIN_HOME_ROUTE['NAME']);
    }

    return $this->redirectToRoute(Routes::HOME_ROUTE['NAME']);
  }

  private function sendRecoveryEmail(UserEntity $user)
  {
    $userFullName = $user->getUserFullName();
    $userEmail = $user->getEmail();
    $resetPasswordLink = '';

    // Create recovery token entity
    $token = Utility::generateRandomToken();
    $recoveryTokenEntity = new RecoveryTokenEntity();
    $recoveryTokenEntity->email = $userEmail;
    $recoveryTokenEntity->token = Utility::hashString($token);
    $recoveryTokenEntity->expiresAt = (new \DateTimeImmutable())->modify('+1 day');

    // Get reset password link
    $resetPasswordLink = $this->generateUrl(
      route: Routes::RESET_PASSWORD_ROUTE['NAME'],
      parameters: [
        'token' => $token,
        'email' => $userEmail
      ],
    );

    // Send recovery email
    $emailService = new EmailService();
    $emailService->setTo($userEmail);
    $emailService->setSubject(Constants::EMAIL_SUBJECTS['recovery']);
    $emailService->setHtml($this->renderView(
      view: TwigTemplate::EMAILS['recovery']['html'],
      parameters: [
        'name' => $userFullName,
        'resetPasswordLink' => $resetPasswordLink
      ]
    ));
    $emailService->setBody($this->renderView(
      view: TwigTemplate::EMAILS['recovery']['text'],
      parameters: [
        'name' => $userFullName,
        'resetPasswordLink' => $resetPasswordLink
      ]
    ));
    $result = $emailService->sendEmail();

    if ($result) {
      // Save recovery token to database
      $this->entityManager->persist($recoveryTokenEntity);
      $this->entityManager->flush();
    }

    return $result;
  }
}
