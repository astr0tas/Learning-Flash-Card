<?php

namespace App\Service;

use App\Config\Constants;
use App\Config\Routes;
use App\Config\TwigTemplate;
use App\DTO\ForgotPasswordDTO;
use App\DTO\LoginDTO;
use App\DTO\LoginWithGoogleDTO;
use App\DTO\RegisterDTO;
use App\DTO\ResetPasswordDTO;
use App\DTO\TokenDTO;
use App\Entity\UserEntity;
use App\Repository\EmailVerificationTokenRepository;
use App\Repository\RecoveryTokenRepository;
use App\Repository\UserRepository;
use App\Utility\Utility;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use League\OAuth2\Client\Provider\Google;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class AuthenticationService extends BaseService
{
  private ?Google $googleOAuthProvider = null;
  private ?RememberMeBadge $rememberMeBadge = null;

  public function __construct(
    #[Autowire(env: 'GOOGLE_CLIENT_ID')] private string $googleOAuthClientId,
    #[Autowire(env: 'GOOGLE_CLIENT_SECRET')] private string $googleOAuthClientSecret,
    private EmailService $emailService,
    private UserRepository $userRepository,
    private RecoveryTokenRepository $recoveryTokenRepository,
    private EmailVerificationTokenRepository $emailVerificationTokenRepository,
    private Security $security,
    private RouterInterface $router,
    private Environment $twig,
  ) {
    $this->googleOAuthProvider = new Google([
      'clientId'     => $this->googleOAuthClientId,
      'clientSecret' => $this->googleOAuthClientSecret,
      'redirectUri' => $this->router->generate(Routes::LOGIN_WITH_GOOGLE_ROUTE_NAME, parameters: [], referenceType: UrlGeneratorInterface::ABSOLUTE_URL),
    ]);

    $this->rememberMeBadge = new RememberMeBadge();
  }

  public function login(LoginDTO $dto, array $data)
  {
    $user = $this->userRepository
      ->findOneBy(criteria: ['email' => $dto->getEmail()]);

    if (!$user || !$user->comparePassword(plainPassword: $dto->getPassword())) {
      $data['error'] = ['general' => [$this->translator->trans(id: 'login.incorrect_credentials')]];
      return $data;
    }

    if (!$this->isEmailVerified($user)) {
      $data['error'] = ['general' => [$this->translator->trans(id: 'login.email_not_verified')]];
      return $data;
    }

    if (!empty($dto->getRememberMe())) {
      $this->rememberMeBadge->enable();
    } else {
      $this->rememberMeBadge->disable();
    }

    $this->security->login($user, Constants::AUTHENTICATOR_NAME, null, [
      $this->rememberMeBadge,
    ]);

    return $data;
  }

  public function loginWithGoogle(LoginWithGoogleDTO $dto)
  {
    $state = $dto->getState();
    $oauth2state = null;

    $oauth2state = $this->session->get(Constants::SESSION_OAUTH2STATE);
    $this->session->remove(Constants::SESSION_OAUTH2STATE);


    if (empty($state)) {
      return false;
    }

    if ($state !== $oauth2state) {
      throw new AccessDeniedHttpException(Constants::MESSAGE_INVALID_CSRF);
    }

    $token = $this->googleOAuthProvider->getAccessToken('authorization_code', [
      'code' => $dto->getCode()
    ]);

    try {
      $payload = $this->googleOAuthProvider->getResourceOwner($token);
    } catch (\Exception $e) {
      if ($this->session instanceof FlashBagAwareSessionInterface) {
        $this->session->getFlashBag()->add('error', ['general' => [$this->translator->trans('login_with_google.error')]]);
      }
      return false;
    }

    $payloadData = $payload->toArray();
    $googleId = $payloadData['sub'];
    $email = $payloadData['email'] ?? '';
    $firstName = $payloadData['given_name'] ?? '';
    $lastName = $payloadData['family_name'] ?? '';

    $user = $this->userRepository
      ->findOneBy(criteria: ['email' => $email]);

    if (!$user) {
      // Create new user
      $user = new UserEntity();
      $user->setEmail($email)
        ->setFirstName($firstName)
        ->setLastName($lastName)
        ->setRoles([Constants::ROLE_USER])
        ->setGoogleId($googleId)
        ->setEmailVerifiedAt(new \DateTimeImmutable());

      $this->entityManager->persist($user);
      $this->entityManager->flush();
    } else if (empty($user->getGoogleId())) {
      $user->setGoogleId($googleId)
        ->setPassword(null);

      $this->entityManager->flush();

      Utility::addNoticeToSessionFlash($this->session, 'info', $this->translator->trans('login_with_google.notice_change_login_method_to_SSO', ['service' => 'Google']));
    }

    $this->security->login($user, Constants::AUTHENTICATOR_NAME, null, [
      $this->rememberMeBadge->enable(),
    ]);

    return true;
  }

  public function forgotPassword(ForgotPasswordDTO $dto, array $data)
  {
    $user = $this->userRepository
      ->findOneBy(criteria: ['email' => $dto->getEmail()]);

    if (!$user) {
      $data['error'] = ['general' => [$this->translator->trans('forgot_password.user_not_found')]];
      return $data;
    }

    // // Check for request spam
    // if ($this->checkRequestSpam(entityClass: RecoveryTokenEntity::class, alias: 't', conditions: ["t.email = '{$user->getEmail()}'"])) {
    //   $data['error'] = ['general' => [$this->translator->trans('general_error.too_many_requests')]];
    //   return $data;
    // }

    if (!$this->sendRecoveryEmail($user)) {
      $data['error'] = ['general' => [$this->translator->trans('general_error.system_error')]];
      return $data;
    }

    return $data;
  }

  private function sendRecoveryEmail(UserEntity $user)
  {
    $userFullName = $user->getUserFullName();
    $userEmail = $user->getEmail();
    $resetPasswordLink = '';

    // Create recovery token entity
    $token = Utility::generateRandomToken();
    $this->recoveryTokenRepository->createToken($userEmail, Utility::hashString($token));

    // Get reset password link
    $resetPasswordLink = $this->router->generate(
      name: Routes::RESET_PASSWORD_ROUTE_NAME,
      parameters: [
        'email' => $userEmail,
        'token' => $token
      ],
      referenceType: UrlGeneratorInterface::ABSOLUTE_URL
    );

    // Send recovery email
    $this->emailService->setTo($userEmail)
      ->setSubject(Constants::EMAIL_SUBJECT_PASSWORD_RECOVERY)
      ->setHtml($this->twig->render(
        name: TwigTemplate::EMAIL_RECOVERY_HTML,
        context: [
          'name' => $userFullName,
          'resetPasswordLink' => $resetPasswordLink
        ]
      ))
      ->setBody($this->twig->render(
        name: TwigTemplate::EMAIL_RECOVERY_TEXT,
        context: [
          'name' => $userFullName,
          'resetPasswordLink' => $resetPasswordLink
        ]
      ));
    $result = $this->emailService->sendEmail();

    if ($result) {
      // Save recovery token to database
      $this->entityManager->flush();
    }

    return $result;
  }

  public function getGoogleOauthAuthorizationUrl()
  {
    return $this->googleOAuthProvider->getAuthorizationUrl();
  }

  public function getGoogleOauthState()
  {
    return $this->googleOAuthProvider->getState();
  }

  public function register(RegisterDTO $dto, array $data)
  {
    $user = new UserEntity();
    $user->setFirstName($dto->getFirstName())
      ->setLastName($dto->getLastName())
      ->setMiddleName($dto->getMiddleName() ?? null)
      ->setRoles([Constants::ROLE_USER])
      ->setEmail($dto->getEmail())
      ->setPassword($dto->getPassword());

    $this->entityManager->persist($user);
    $this->entityManager->flush();

    if (!$this->sendVerificationEmail($user)) {
      $data['error'] = ['general' => [$this->translator->trans('general_error.system_error')]];
      return $data;
    }

    return $data;
  }

  private function sendVerificationEmail(UserEntity $user)
  {
    $userEmail = $user->getEmail();
    $userFullName = $user->getUserFullName();

    $token = Utility::generateRandomToken();

    $this->emailVerificationTokenRepository->createToken($userEmail, Utility::hashString($token));

    // Get email verification link
    $emailVerifyLink = $this->router->generate(
      name: Routes::EMAIL_VERIFICATION_ROUTE_NAME,
      parameters: [
        'email' => $userEmail,
        'token' => $token
      ],
      referenceType: UrlGeneratorInterface::ABSOLUTE_URL
    );

    // Send email verification
    $this->emailService->setTo($userEmail)
      ->setSubject(Constants::EMAIL_SUBJECT_EMAIL_VERIFICATION)
      ->setHtml($this->twig->render(
        name: TwigTemplate::EMAIL_VERIFICATION_HTML,
        context: [
          'name' => $userFullName,
          'emailVerifyLink' => $emailVerifyLink
        ]
      ))
      ->setBody($this->twig->render(
        name: TwigTemplate::EMAIL_VERIFICATION_TEXT,
        context: [
          'name' => $userFullName,
          'emailVerifyLink' => $emailVerifyLink
        ]
      ));

    $result = $this->emailService->sendEmail();

    if ($result) {
      $this->entityManager->flush();
    }

    return $result;
  }

  private function isEmailVerified(UserEntity $user)
  {
    if (!empty($user->getEmailVerifiedAt())) {
      return true;
    }

    // Check if email verification token exists and is still valid, if not create another one
    // if (empty($this->emailVerificationTokenRepository->getLatestToken($user->getEmail()))) {
    $this->sendVerificationEmail($user);
    // }

    return false;
  }

  public function checkVerificationToken(TokenDTO $dto, array $data)
  {
    $tokenEntity = $this->emailVerificationTokenRepository->getLatestToken($dto->getEmail());

    if (empty($tokenEntity) || !Utility::compareHash(Utility::hashString($dto->getToken()), $tokenEntity->token)) {
      $data['error'] = ['general' => [$this->translator->trans('verify_email.invalid_or_expire_url')]];
      return $data;
    }

    $tokenEntity->isConsumed = true;
    $this->emailVerificationTokenRepository->clearUnusedTokens($dto->getEmail());

    $userEntity = $this->userRepository->findOneBy(['email' => $dto->getEmail()]);

    if (empty($userEntity)) {
      $data['error'] = ['general' => [$this->translator->trans('verify_email.user_not_found')]];
      return $data;
    }

    $userEntity->setEmailVerifiedAt(new \DateTimeImmutable());

    $this->entityManager->flush();

    return $data;
  }

  public function checkRecoveryToken(TokenDTO $dto, array $data)
  {
    $tokenEntity = $this->recoveryTokenRepository->getLatestToken($dto->getEmail());

    if (empty($tokenEntity) || !Utility::compareHash(Utility::hashString($dto->getToken()), $tokenEntity->token)) {
      $data['error'] = ['general' => [$this->translator->trans('reset_password.invalid_or_expire_url')]];
      return $data;
    }

    return $data;
  }

  public function resetPassowrd(ResetPasswordDTO $dto, array $data)
  {
    $tokenEntity = $this->recoveryTokenRepository->getLatestToken($dto->getEmail());

    if (empty($tokenEntity) || !Utility::compareHash(Utility::hashString($dto->getToken()), $tokenEntity->token)) {
      $data['error'] = ['general' => [$this->translator->trans('reset_password.invalid_or_expire_url')]];
      return $data;
    }
    $tokenEntity->isConsumed = true;
    $this->recoveryTokenRepository->clearUnusedTokens($dto->getEmail());

    $user = $this->userRepository->findOneBy(['email' => $dto->getEmail()]);

    if (empty($user)) {
      $data['error'] = ['general' => [$this->translator->trans('reset_password.user_not_found')]];
      return $data;
    }
    $user->setPassword($dto->getNewPassword());

    $this->entityManager->flush();

    return $data;
  }

  public function logout()
  {
    return $this->security->logout(false);
  }
}
