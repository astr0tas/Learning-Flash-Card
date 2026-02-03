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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use League\OAuth2\Client\Provider\Google;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class AuthenticationController extends BaseController
{
  private ?Google $googleOAuthProvider = null;

  // Inject the value directly into the constructor
  public function __construct(
    #[Autowire(env: 'GOOGLE_CLIENT_ID')] private string $googleOAuthClientId,
    #[Autowire(env: 'GOOGLE_CLIENT_SECRET')] private string $googleOAuthClientSecret,
    private Security $security,
    private RouterInterface $router,
  ) {
    $this->googleOAuthProvider = new Google([
      'clientId'     => $this->googleOAuthClientId,
      'clientSecret' => $this->googleOAuthClientSecret,
      'redirectUri' => $this->router->generate(Routes::LOGIN_WITH_GOOGLE_ROUTE_NAME, parameters: [], referenceType: UrlGeneratorInterface::ABSOLUTE_URL),
    ]);
  }

  #[Route(path: Routes::LOGIN_ROUTE_URL, name: Routes::LOGIN_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function LoginAction()
  {
    if ($this->session instanceof FlashBagAwareSessionInterface) {
      $error = $this->session->getFlashBag()->get('error', []);
      $error = count($error) > 0 ? $error[0] : [];
    }

    if ($this->getUser()) {
      return $this->redirectUserToHome();
    }

    return $this->renderLogin(data: ['error' => $error]);
  }

  #[Route(path: Routes::LOGIN_SUBMIT_ROUTE_URL, name: Routes::LOGIN_SUBMIT_ROUTE_NAME, methods: [Request::METHOD_POST])]
  public function LoginSubmitAction(Request $request)
  {
    if ($this->getUser()) {
      return $this->redirectUserToHome();
    }

    // Store post data and errors for flash messages
    $data = [];

    // Handle login submission
    $postData = $request->request->all();

    // Echo back input values except password
    $data['email'] = $postData['email'] ?? '';
    $data['remember_me'] = isset($postData['remember_me']) ? 'checked' : '';

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
      $data['error'] = $errors;
      return $this->renderLogin(data: $data, response: $this->unprocessableEntityResponse);
    }

    $user = $this->entityManager
      ->getRepository(UserEntity::class)
      ->findOneBy(criteria: ['email' => $postData['email']]);

    if (!$user || !$user->comparePassword($postData['password'])) {
      $data['error'] = ['general' => [$this->translator->trans('login.incorrect_credentials')]];
      return $this->renderLogin(data: $data, response: $this->unprocessableEntityResponse);
    }

    $rememberMeBadge = new RememberMeBadge();

    if (isset($postData['remember_me'])) {
      $rememberMeBadge->enable();
    } else {
      $rememberMeBadge->disable();
    }

    $this->security->login($user, Constants::AUTHENTICATOR_NAME, null, [
      $rememberMeBadge,
    ]);

    return $this->redirectUserToHome();
  }

  #[Route(path: Routes::LOGIN_WITH_GOOGLE_ROUTE_URL, name: Routes::LOGIN_WITH_GOOGLE_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function LoginWithGoogleAction(Request $request)
  {
    if ($this->getUser()) {
      return $this->redirectUserToHome();
    }

    $state = $request->get('state');
    $oauth2state = null;
    if ($this->session instanceof FlashBagAwareSessionInterface) {
      $oauth2state = $this->session->get(Constants::SESSION_OAUTH2STATE);
      $this->session->remove(Constants::SESSION_OAUTH2STATE);
    }

    if (empty($state)) {
      return $this->redirectToRoute(Routes::LOGIN_ROUTE_NAME);
    }

    if ($state !== $oauth2state) {
      throw new AccessDeniedHttpException(Constants::MESSAGE_INVALID_CSRF);
    }

    $token = $this->googleOAuthProvider->getAccessToken('authorization_code', [
      'code' => $request->get('code')
    ]);

    try {
      $payload = $this->googleOAuthProvider->getResourceOwner($token);
    } catch (\Exception $e) {
      $this->addFlash('error', ['general' => [$this->translator->trans('login_with_google.error')]]);
      return $this->redirectToRoute(Routes::LOGIN_ROUTE_NAME);
    }

    $payloadData = $payload->toArray();
    $email = $payloadData['email'] ?? '';
    $firstName = $payloadData['given_name'] ?? '';
    $lastName = $payloadData['family_name'] ?? '';

    $user = $this->entityManager
      ->getRepository(UserEntity::class)
      ->findOneBy(criteria: ['email' => $email]);

    if (!$user) {
      // Create new user
      $user = new UserEntity();
      $user->setEmail($email);
      $user->setFirstName($firstName);
      $user->setLastName($lastName);
      $user->setPassword(Constants::GOOGLE_OAUTH_PASSWORD, false); // Random password
      $user->setRoles([Constants::ROLE_USER]);

      $this->entityManager->persist($user);
      $this->entityManager->flush();
    }

    $request->request->set('remember_me', 'on');

    $this->security->login($user, Constants::AUTHENTICATOR_NAME, null, [
      (new RememberMeBadge())->enable(),
    ]);

    return $this->redirectUserToHome();
  }

  #[Route(path: Routes::LOGOUT_ROUTE_URL, name: Routes::LOGOUT_ROUTE_NAME, methods: [Request::METHOD_POST])]
  public function LogoutAction()
  {
    return $this->security->logout();
  }

  #[Route(path: Routes::REGISTER_ROUTE_URL, name: Routes::REGISTER_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function RegisterAction()
  {
    if ($this->getUser()) {
      return $this->redirectUserToHome();
    }

    return $this->render(view: TwigTemplate::PAGE_REGISTER);
  }

  #[Route(path: Routes::FORGOT_PASSWORD_ROUTE_URL, name: Routes::FORGOT_PASSWORD_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function ForgotPasswordAction()
  {
    if ($this->getUser()) {
      return $this->redirectUserToHome();
    }

    return $this->render(view: TwigTemplate::PAGE_FORGOT_PASSWORD);
  }

  #[Route(path: Routes::FORGOT_PASSWORD_SUBMIT_ROUTE_URL, name: Routes::FORGOT_PASSWORD_SUBMIT_ROUTE_NAME, methods: [Request::METHOD_POST])]
  public function ForgotPasswordSubmitAction(Request $request, EmailService $emailService)
  {
    if ($this->getUser()) {
      return $this->redirectUserToHome();
    }

    // Store post data and errors for flash messages
    $data = [];

    // Handle post data
    $postData = $request->request->all();

    // Echo back input values
    $data['email'] = $postData['email'] ?? '';

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
      $data['error'] = $errors;
      return $this->render(view: TwigTemplate::PAGE_FORGOT_PASSWORD, parameters: $data, response: $this->unprocessableEntityResponse);
    }

    $user = $this->entityManager
      ->getRepository(UserEntity::class)
      ->findOneBy(criteria: ['email' => $postData['email']]);

    if (!$user) {
      $data['error'] = ['general' => [$this->translator->trans('forgot_password.user_not_found')]];
      return $this->render(view: TwigTemplate::PAGE_FORGOT_PASSWORD, parameters: $data, response: $this->unprocessableEntityResponse);
    }

    if ($user->getPassword() === Constants::GOOGLE_OAUTH_PASSWORD) {
      $data['error'] = ['general' => [$this->translator->trans('forgot_password.oauth_user')]];
      return $this->render(view: TwigTemplate::PAGE_FORGOT_PASSWORD, parameters: $data, response: $this->unprocessableEntityResponse);
    }

    // Check for request spam
    if ($this->checkRequestSpam(entityClass: RecoveryTokenEntity::class, alias: 't', conditions: ["t.email = '{$user->getEmail()}'"])) {
      $data['error'] = ['general' => [$this->translator->trans('general_error.too_many_requests')]];
      return $this->render(view: TwigTemplate::PAGE_FORGOT_PASSWORD, parameters: $data, response: $this->unprocessableEntityResponse);
    }

    if (!$this->sendRecoveryEmail($user, $emailService)) {
      $data['error'] = ['general' => [$this->translator->trans('forgot_password.system_error')]];
      return $this->render(view: TwigTemplate::PAGE_FORGOT_PASSWORD, parameters: $data, response: $this->unprocessableEntityResponse);
    }

    return $this->render(view: TwigTemplate::PAGE_RECOVERY_EMAIL_SENT);
  }

  #[Route(path: Routes::RESET_PASSWORD_ROUTE_URL, name: Routes::RESET_PASSWORD_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function ResetPasswordAction()
  {
    if ($this->getUser()) {
      return $this->redirectUserToHome();
    }

    return $this->render(view: TwigTemplate::PAGE_RESET_PASSWORD);
  }

  #[Route(path: Routes::EMAIL_VERIFICATION_ROUTE_URL, name: Routes::EMAIL_VERIFICATION_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function EmailVerificationAction(Request $request)
  {
    if ($this->getUser()) {
      return $this->redirectUserToHome();
    }
  }

  private function redirectUserToHome()
  {
    $user = $this->getUser();

    if ($this->isGranted(Constants::ROLE_ADMIN, $user)) {
      return $this->redirectToRoute(Routes::ADMIN_HOME_ROUTE_NAME);
    }

    return $this->redirectToRoute(Routes::HOME_ROUTE_NAME);
  }

  private function sendRecoveryEmail(UserEntity $user, EmailService $emailService)
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
      route: Routes::RESET_PASSWORD_ROUTE_NAME,
      parameters: [
        'token' => $token,
        'email' => $userEmail
      ],
    );

    // Send recovery email
    $emailService->setTo($userEmail);
    $emailService->setSubject(Constants::EMAIL_SUBJECT_PASSWORD_RECOVERY);
    $emailService->setHtml($this->renderView(
      view: TwigTemplate::EMAIL_RECOVERY_HTML,
      parameters: [
        'name' => $userFullName,
        'resetPasswordLink' => $resetPasswordLink
      ]
    ));
    $emailService->setBody($this->renderView(
      view: TwigTemplate::EMAIL_RECOVERY_TEXT,
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

  private function renderLogin(array $data = [], ?Response $response = null)
  {
    $authUrl = $this->googleOAuthProvider->getAuthorizationUrl();
    $data['google_oauth_url'] = $authUrl;
    $this->session->set(Constants::SESSION_OAUTH2STATE, $this->googleOAuthProvider->getState());

    return $this->render(view: TwigTemplate::PAGE_LOGIN, parameters: $data, response: $response);
  }
}
