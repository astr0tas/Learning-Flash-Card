<?php

namespace App\Controller;

use App\Config\Constants;
use App\DTO\ForgotPasswordDTO;
use App\DTO\LoginDTO;
use App\DTO\LoginWithGoogleDTO;
use App\Service\EmailService;
use Symfony\Component\Routing\Attribute\Route;
use App\Config\Routes;
use App\Config\TwigTemplate;
use App\Service\AuthenticationService;
use App\Utility\Utility;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationController extends BaseController
{
  // Inject the value directly into the constructor
  public function __construct(
    private AuthenticationService $service,
  ) {}

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

    // Pass the form data to a DTO
    $dto = new LoginDTO();
    Utility::mapArrayToDTO($postData, $dto);

    // Echo back input values except password
    $data['email'] = $dto->email ?? '';
    $data['remember_me'] = !empty($dto->rememberMe) ? 'checked' : '';

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
    $errors = Utility::validateInputDTO($dto, $fields);

    if (count($errors) > 0) {
      $data['error'] = $errors;
      return $this->renderLogin(data: $data, response: $this->unprocessableEntityResponse);
    }

    $data = $this->service->login($dto, $data);

    if (!empty($data['error'])) {
      return $this->renderLogin(data: $data, response: $this->unprocessableEntityResponse);
    }

    return $this->redirectUserToHome();
  }

  #[Route(path: Routes::LOGIN_WITH_GOOGLE_ROUTE_URL, name: Routes::LOGIN_WITH_GOOGLE_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function LoginWithGoogleAction(Request $request)
  {
    if ($this->getUser()) {
      return $this->redirectUserToHome();
    }

    $requestParams = $request->query->all();
    $dto = new LoginWithGoogleDTO();
    Utility::mapArrayToDTO($requestParams, $dto);

    if (!$this->service->loginWithGoogle($dto)) {
      return $this->redirectToRoute(Routes::LOGIN_ROUTE_NAME);
    }

    return $this->redirectUserToHome();
  }

  #[Route(path: Routes::LOGOUT_ROUTE_URL, name: Routes::LOGOUT_ROUTE_NAME, methods: [Request::METHOD_POST])]
  public function LogoutAction()
  {
    if (!$this->getUser()) {
      return $this->redirectUserToHome();
    }
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

    // Map post data to DTO
    $dto = new ForgotPasswordDTO();
    Utility::mapArrayToDTO($postData, $dto);

    // Echo back input values
    $data['email'] = $dto->email ?? '';

    // Validate post data
    $fields = [
      'email'    => [new Assert\NotBlank(
        message: $this->translator->trans('validation.email.not_blank')
      ), new Assert\Email(
        message: $this->translator->trans('validation.email.invalid')
      )],
    ];
    $errors = Utility::validateInputDTO($dto, $fields);

    if (count($errors) > 0) {
      $data['error'] = $errors;
      return $this->render(view: TwigTemplate::PAGE_FORGOT_PASSWORD, parameters: $data, response: $this->unprocessableEntityResponse);
    }

    $data = $this->service->forgotPassword($dto, $data);

    if (!empty($data['error'])) {
      return $this->render(view: TwigTemplate::PAGE_FORGOT_PASSWORD, parameters: $data, response: $this->unprocessableEntityResponse);
    }

    return $this->render(view: TwigTemplate::PAGE_RECOVERY_EMAIL_SENT, parameters: $data);
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

  private function renderLogin(array $data = [], ?Response $response = null)
  {
    $authUrl = $this->service->getGoogleOauthAuthorizationUrl();
    $data['google_oauth_url'] = $authUrl;
    $this->session->set(Constants::SESSION_OAUTH2STATE, $this->service->getGoogleOauthState());

    return $this->render(view: TwigTemplate::PAGE_LOGIN, parameters: $data, response: $response);
  }
}
