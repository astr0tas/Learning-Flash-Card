<?php

namespace App\Controller\User;

use App\Config\Routes;
use App\Config\TwigTemplate;
use App\Controller\BaseController;
use App\DTO\NewBagDTO;
use App\Service\CardBagService;
use App\Utility\ClassUtility;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Service\Attribute\Required;

class CardBagController extends BaseController
{
  #[Required]
  private CardBagService $service;

  #[Route(path: Routes::CARD_BAG_ROUTE_URL, name: Routes::CARD_BAG_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function index()
  {
    $error = $this->getErrorFlash();

    return $this->render(view: TwigTemplate::PAGE_USER_CARD_BAG, parameters: ['error' => $error]);
  }

  #[Route(path: Routes::CARD_BAG_DETAIL_ROUTE_URL, name: Routes::CARD_BAG_DETAIL_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function bagDetail(int $id)
  {
    $error = $this->getErrorFlash();

    return $this->render(view: TwigTemplate::PAGE_USER_CARD_BAG, parameters: ['error' => $error]);
  }

  #[Route(path: Routes::CREATE_NEW_BAG_ROUTE_URL, name: Routes::CCREATE_NEW_BAG_ROUTE_NAME, methods: [Request::METHOD_GET, Request::METHOD_POST])]
  public function createNewBag(Request $request)
  {
    $flashBag = $this->getFlashBag();

    // Get the previous route to redirect back to it
    $previousRoute = $request->headers->get('referer');

    if ($request->getMethod() === Request::METHOD_GET) {
      return $this->redirectToRoute(route: $previousRoute);
    }

    // Handle login submission
    $postData = $request->request->all();

    // Pass the form data to a DTO
    $dto = new NewBagDTO();
    ClassUtility::mapArrayToDTO($postData, $dto);

    // Validate post data
    $fields = [
      'newBagName' => [
        new Assert\NotBlank(message: $this->translator->trans('validation.new_bag.name_not_blank')),
      ],
    ];
    $error = ClassUtility::validateInputDTO($dto, $fields);

    if (count($error) > 0) {
      $flashBag->add('newBagError', [
        'newBagNameError' => $error
      ]);
      return $this->redirectToRoute(route: $previousRoute);
    }
  }

  #[Route(path: Routes::CREATE_NEW_CARD_ROUTE_URL, name: Routes::CCREATE_NEW_CARD_ROUTE_NAME, methods: [Request::METHOD_GET, Request::METHOD_POST])]
  public function createNewCard(Request $request)
  {
    // Get the previous route to redirect back to it
    $previousRoute = $request->headers->get('referer');

    if ($request->getMethod() === Request::METHOD_GET) {
      return $this->redirectToRoute(route: $previousRoute);
    }

    return $this->render(view: TwigTemplate::PAGE_USER_CARD_BAG);
  }

  /**
   * This function will focus on getting flash error data emitted from other functions in this controller
   * @return void
   */
  private function getErrorFlash(): array
  {
    $errors = [];

    $flashBag = $this->getFlashBag();

    // Get flash errors when creating new bag
    if ($flashBag->has('newBagError')) {
      $errors['newBagError'] = true;

      $flashErrors = $flashBag->get('newBagError');
      $errors['newBagNameError'] = $flashErrors['newBagNameError'];
    }

    // Get flash errors when creating new card
    if ($flashBag->has('newCardError')) {
      $errors['newCardError'] = true;
    }

    return $errors;
  }
}
