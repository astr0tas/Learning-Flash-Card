<?php

namespace App\Controller;

use App\Config\Constants;
use App\DTO\ForgotPasswordDTO;
use App\DTO\LoginDTO;
use App\DTO\LoginWithGoogleDTO;
use App\DTO\ResetPasswordDTO;
use App\Service\EmailService;
use Symfony\Component\Routing\Attribute\Route;
use App\Config\Routes;
use App\Config\TwigTemplate;
use App\DTO\RegisterDTO;
use App\DTO\TokenDTO;
use App\Service\AuthenticationService;
use App\Utility\ClassUtility;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\Validator\Constraints\PasswordStrength;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AuthenticationController extends BaseController
{
  // Inject the value directly into the constructor
  public function __construct(
    private AuthenticationService $service,
  ) {}

  #[Route(path: Routes::LOGIN_ROUTE_URL, name: Routes::LOGIN_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function LoginAction()
  {
    $error = $this->getFlashBag()->get('error', []);
    $error = count($error) > 0 ? $error[0] : [];

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
    ClassUtility::mapArrayToDTO($postData, $dto);

    // Echo back input values except password
    $data['email'] = $dto->getEmail() ?? '';
    $data['remember_me'] = !empty($dto->getRememberMe()) ? 'checked' : '';

    // Validate post data
    $fields = [
      'email' => [
        new Assert\NotBlank(message: $this->translator->trans('validation.email.not_blank')),
        new Assert\Email(message: $this->translator->trans('validation.email.invalid'))
      ],
      'password' => [new Assert\NotBlank(message: $this->translator->trans('validation.password.not_blank'))],
    ];
    $error = ClassUtility::validateInputDTO($dto, $fields);

    if (count($error) > 0) {
      $data['error'] = $error;
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
    ClassUtility::mapArrayToDTO($requestParams, $dto);

    if (!$this->service->loginWithGoogle($dto)) {
      return $this->redirectToRoute(Routes::LOGIN_ROUTE_NAME);
    }

    return $this->redirectUserToHome();
  }

  #[Route(path: Routes::LOGOUT_ROUTE_URL, name: Routes::LOGOUT_ROUTE_NAME, methods: [Request::METHOD_POST])]
  public function LogoutAction()
  {
    if ($this->getUser()) {
      $response = $this->service->logout();
      $response->isRedirect(Routes::LOGIN_ROUTE_URL);
      return $response;
    }

    return $this->redirect(Routes::LOGIN_ROUTE_URL);
  }

  #[Route(path: Routes::REGISTER_ROUTE_URL, name: Routes::REGISTER_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function RegisterAction()
  {
    if ($this->getUser()) {
      return $this->redirectUserToHome();
    }

    return $this->render(view: TwigTemplate::PAGE_REGISTER);
  }

  #[Route(path: Routes::REGISTER_SUBMIT_ROUTE_URL, name: Routes::REGISTER_SUBMIT_ROUTE_NAME, methods: [Request::METHOD_POST])]
  public function RegisterSubmitAction(Request $request)
  {
    if ($this->getUser()) {
      return $this->redirectUserToHome();
    }

    // Store post data and errors for flash messages
    $data = [];

    // Handle post data
    $postData = $request->request->all();

    // Map post data to DTO
    $dto = new RegisterDTO();
    ClassUtility::mapArrayToDTO($postData, $dto);

    // Echo back input values
    $data['first_name'] = $dto->getFirstName() ?? '';
    $data['last_name'] = $dto->getLastName() ?? '';
    $data['middle_name'] = $dto->getMiddleName() ?? '';
    $data['email'] = $dto->getEmail() ?? '';

    // Validate post data
    $fields = [
      'firstName' => [new Assert\NotBlank(message: $this->translator->trans('validation.first_name.not_blank'))],
      'lastName' => [new Assert\NotBlank(message: $this->translator->trans('validation.last_name.not_blank'))],
      'email' => [
        new Assert\NotBlank(message: $this->translator->trans('validation.email.not_blank')),
        new Assert\Email(message: $this->translator->trans('validation.email.invalid')),
        new Assert\Callback(callback: function (string $data, ExecutionContextInterface $context) {
          if ($this->service->checkEmailExist(email: $data)) {
            $context->buildViolation($this->translator->trans('validation.email.email_exist'))->addViolation();
          }
        })
      ],
      'password' => [
        new Assert\NotBlank(message: $this->translator->trans('validation.password.not_blank')),
        // new Assert\PasswordStrength(minScore: PasswordStrength::STRENGTH_STRONG, message: $this->translator->trans('validation.password.invalid'))
        new Assert\Callback(function (string $password, ExecutionContextInterface $context) {
          // (?=.*[a-z]) -> At least 1 Lowercase
          // (?=.*[A-Z]) -> At least 1 Uppercase
          // (?=.*\d)    -> At least 1 Digit
          // (?=.*[\W_]) -> At least 1 Special Character (Symbol)
          // .{8,}      -> At least 8 characters long
          $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';

          if (!preg_match($regex, $password)) {
            $context->buildViolation($this->translator->trans('validation.password.invalid'))->addViolation();
          }
        })
      ],
      'confirmPassword' => [
        new Assert\NotBlank(message: $this->translator->trans('validation.confirm_password.not_blank')),
      ],
    ];
    $globals = [
      new Assert\Callback(callback: function (array $data, ExecutionContextInterface $context) {
        if ($data['password'] !== $data['confirmPassword']) {
          $context->buildViolation($this->translator->trans('validation.confirm_password.mismatch'))
            ->atPath('[confirmPassword]')
            ->addViolation();
        }
      })
    ];
    $error = ClassUtility::validateInputDTO($dto, $fields, $globals);

    if (count($error) > 0) {
      $data['error'] = $error;
      return $this->render(view: TwigTemplate::PAGE_REGISTER, parameters: $data, response: $this->unprocessableEntityResponse);
    }

    $data = $this->service->register($dto, $data);

    if (!empty($data['error'])) {
      return $this->render(view: TwigTemplate::PAGE_REGISTER, parameters: $data, response: $this->unprocessableEntityResponse);
    }

    $data['success'] = true;
    return $this->render(view: TwigTemplate::PAGE_REGISTER, parameters: $data);
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
    ClassUtility::mapArrayToDTO($postData, $dto);

    // Echo back input values
    $data['email'] = $dto->getEmail() ?? '';

    // Validate post data
    $fields = [
      'email' => [
        new Assert\NotBlank(message: $this->translator->trans('validation.email.not_blank')),
        new Assert\Email(message: $this->translator->trans('validation.email.invalid'))
      ],
    ];
    $error = ClassUtility::validateInputDTO($dto, $fields);

    if (count($error) > 0) {
      $data['error'] = $error;
      return $this->render(view: TwigTemplate::PAGE_FORGOT_PASSWORD, parameters: $data, response: $this->unprocessableEntityResponse);
    }

    $data = $this->service->forgotPassword($dto, $data);

    if (!empty($data['error'])) {
      return $this->render(view: TwigTemplate::PAGE_FORGOT_PASSWORD, parameters: $data, response: $this->unprocessableEntityResponse);
    }

    $data['success'] = true;
    return $this->render(view: TwigTemplate::PAGE_FORGOT_PASSWORD, parameters: $data);
  }

  #[Route(path: Routes::RESET_PASSWORD_ROUTE_URL, name: Routes::RESET_PASSWORD_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function ResetPasswordAction(Request $request)
  {
    if ($this->getUser()) {
      return $this->redirectUserToHome();
    }

    $requestParams = $request->query->all();

    $dto = new TokenDTO();
    ClassUtility::mapArrayToDTO($requestParams, $dto);

    $data = [];

    $data = $this->service->checkRecoveryToken($dto, $data);

    if (empty($data['error'])) {
      $data['input_mode'] = true;
      $data['email'] = $dto->getEmail();
      $data['token'] = $dto->getToken();
    }

    return $this->render(view: TwigTemplate::PAGE_RESET_PASSWORD, parameters: $data);
  }

  #[Route(path: Routes::RESET_PASSWORD_SUBMIT_ROUTE_URL, name: Routes::RESET_PASSWORD_SUBMIT_ROUTE_NAME, methods: [Request::METHOD_POST])]
  public function ResetPasswordSubmitAction(Request $request)
  {
    if ($this->getUser()) {
      return $this->redirectUserToHome();
    }

    $postData = $request->request->all();

    $dto = new ResetPasswordDTO();
    ClassUtility::mapArrayToDTO($postData, $dto);

    $fields = [
      'email' => [
        new Assert\NotBlank(message: $this->translator->trans('validation.email.not_blank')),
        new Assert\Email(message: $this->translator->trans('validation.email.invalid')),
      ],
      'newPassword' => [
        new Assert\NotBlank(message: $this->translator->trans('validation.new_password.not_blank')),
        new Assert\Callback(function (string $password, ExecutionContextInterface $context) {
          // (?=.*[a-z]) -> At least 1 Lowercase
          // (?=.*[A-Z]) -> At least 1 Uppercase
          // (?=.*\d)    -> At least 1 Digit
          // (?=.*[\W_]) -> At least 1 Special Character (Symbol)
          // .{8,}      -> At least 8 characters long
          $regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';

          if (!preg_match($regex, $password)) {
            $context->buildViolation($this->translator->trans('validation.new_password.invalid'))->addViolation();
          }
        })
      ],
      'confirmNewPassword' => [
        new Assert\NotBlank(message: $this->translator->trans('validation.confirm_new_password.not_blank')),
      ],
    ];
    $globals = [
      new Assert\Callback(callback: function (array $data, ExecutionContextInterface $context) {
        if ($data['newPassword'] !== $data['confirmNewPassword']) {
          $context->buildViolation($this->translator->trans('validation.confirm_new_password.mismatch'))
            ->atPath('[confirmNewPassword]')
            ->addViolation();
        }
      })
    ];
    $error = ClassUtility::validateInputDTO($dto, $fields, $globals);

    $data = [];
    $data['email'] = $dto->getEmail();
    $data['token'] = $dto->getToken();

    if (count($error) > 0) {
      $data['error'] = $error;
      $data['input_mode'] = true;
      return $this->render(view: TwigTemplate::PAGE_RESET_PASSWORD, parameters: $data);
    }

    $data = $this->service->resetPassowrd($dto, $data);

    if (!empty($data['error'])) {
      $data['input_mode'] = true;
    }

    return $this->render(view: TwigTemplate::PAGE_RESET_PASSWORD, parameters: $data);
  }

  #[Route(path: Routes::EMAIL_VERIFICATION_ROUTE_URL, name: Routes::EMAIL_VERIFICATION_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function EmailVerificationAction(Request $request)
  {
    if ($this->getUser()) {
      return $this->redirectUserToHome();
    }

    $requestParams = $request->query->all();

    $dto = new TokenDTO();
    ClassUtility::mapArrayToDTO($requestParams, $dto);

    $data = [];

    $data = $this->service->checkVerificationToken($dto, $data);

    return $this->render(view: TwigTemplate::PAGE_VERIFY_EMAIL, parameters: $data);
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
