<?php

namespace App\Controller\User;

use App\Config\Constants;
use App\Config\Constraints;
use App\Config\Routes;
use App\Config\TwigTemplate;
use App\Controller\BaseController;
use App\DTO\EditCardDTO;
use App\DTO\NewBagDTO;
use App\DTO\NewCardDTO;
use App\DTO\SelectObjectDTO;
use App\Service\CardBagService;
use App\Utility\ClassUtility;
use App\Utility\Utility;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CardBagController extends BaseController
{
  public function __construct(private CardBagService $service) {}

  #[Route(path: Routes::CARD_BAG_ROUTE_URL, name: Routes::CARD_BAG_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function index()
  {
    $error = $this->getErrorFlash();
    $breadcrumb = [['icon' => $this->renderView('icons/folder.svg'), 'label' => $this->translator->trans('menu.card_bag'), 'url' => Routes::CARD_BAG_ROUTE_URL, 'id' => null]];

    return $this->render(view: TwigTemplate::PAGE_USER_CARD_BAG, parameters: [
      'error' => $error,
      'bagList' => $this->service->getBagList(null),
      'cardList' => $this->service->getCardList(null),
      'breadcrumb' => $breadcrumb,
    ]);
  }

  #[Route(path: Routes::CARD_BAG_DETAIL_ROUTE_URL, name: Routes::CARD_BAG_DETAIL_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function bagDetail(int $id)
  {
    $error = $this->getErrorFlash();
    $bag = $this->service->getBag($id);

    if ($bag === null) {
      throw $this->createNotFoundException($this->translator->trans('card_bag.bag_not_found'));
    }

    $cards = $bag->getCardEntities();
    $childrenBags = $bag->getChildrenCardBagEntities();
    $bagTree = $this->service->getBagTree($id);

    // Convert the bag tree to breadcrumbs array
    $breadcrumb = [['icon' => $this->renderView('icons/folder.svg'), 'label' => $this->translator->trans('menu.card_bag'), 'url' => Routes::CARD_BAG_ROUTE_URL, 'id' => null]];
    $breadcrumb = $this->service->parseBagTreeToBreadcrumb($bagTree, $breadcrumb);

    return $this->render(view: TwigTemplate::PAGE_USER_CARD_BAG, parameters: [
      'error' => $error,
      'bagList' => $childrenBags,
      'cardList' => $cards,
      'bag' => $bag,
      'breadcrumb' => $breadcrumb
    ]);
  }

  #[Route(path: Routes::CREATE_NEW_BAG_ROUTE_URL, name: Routes::CREATE_NEW_BAG_ROUTE_NAME, methods: [Request::METHOD_POST])]
  public function createNewBag(Request $request)
  {
    $flashBag = $this->getFlashBag();

    // Get the previous route to redirect back to it
    $previousRoute = $request->headers->get('referer') ?? Routes::CARD_BAG_ROUTE_URL;

    $postData = $request->request->all();

    // Pass the form data to a DTO
    $dto = new NewBagDTO();
    ClassUtility::mapArrayToDTO($postData, $dto);

    // Validate post data
    $fields = [
      'newBagName' => [
        new Assert\NotBlank(message: $this->translator->trans('validation.new_bag.name_not_blank')),
        new Assert\Length(max: Constraints::CARD_BAG_NAME_MAX_LENGTH, maxMessage: $this->translator->trans('validation.new_bag.name_too_long', ['limit' => Constraints::CARD_BAG_NAME_MAX_LENGTH])),
      ]
    ];
    $globals = [
      new Assert\Callback(callback: function (array $data, ExecutionContextInterface $context) {
        if (count($this->service->getBagByNameAndParentId($data['newBagName'], $data['parentBag'] ?: null)) > 0) {
          $context->buildViolation($this->translator->trans('validation.new_bag.name_exist'))
            ->atPath('[newBagName]')
            ->addViolation();
        }
      })
    ];
    $error = ClassUtility::validateInputDTO($dto, $fields, $globals);

    if (count($error) > 0) {
      $flashBag->add('newBagError', $error);
      $flashBag->add('newBagName', $dto->getNewBagName());
      return $this->redirect($previousRoute);
    }

    // $newBag = $this->service->addNewBag($dto);
    $this->service->addNewBag($dto);

    Utility::addNoticeToSessionFlash($this->session, 'success', $this->translator->trans('card_bag.new_bag_created'));

    // $redirectUrl = str_replace('{id}', $newBag->getId(), Routes::CARD_BAG_DETAIL_ROUTE_URL);
    // return $this->redirect($redirectUrl);
    return $this->redirect($previousRoute);
  }

  #[Route(path: Routes::CREATE_NEW_CARD_ROUTE_URL, name: Routes::CREATE_NEW_CARD_ROUTE_NAME, methods: [Request::METHOD_POST])]
  public function createNewCard(Request $request)
  {
    $flashBag = $this->getFlashBag();

    // Get the previous route to redirect back to it
    $previousRoute = $request->headers->get('referer') ?? Routes::CARD_BAG_ROUTE_URL;

    $postData = $request->request->all();

    // Pass the form data to a DTO
    $dto = new NewCardDTO();
    ClassUtility::mapArrayToDTO($postData, $dto);

    // Validate post data
    $fields = [
      'title' => [
        new Assert\NotBlank(message: $this->translator->trans('validation.card.title_not_blank')),
        new Assert\Length(max: Constraints::CARD_TITLE_MAX_LENGTH, maxMessage: $this->translator->trans('validation.card.title_too_long', ['limit' => Constraints::CARD_TITLE_MAX_LENGTH])),
      ],
      'subtitle' => [
        new Assert\Length(max: Constraints::CARD_SUBTITLE_MAX_LENGTH, maxMessage: $this->translator->trans('validation.card.subtitle_too_long', ['limit' => Constraints::CARD_SUBTITLE_MAX_LENGTH]))
      ],
      'description' => [
        new Assert\Length(max: Constraints::CARD_DESCRIPTION_MAX_LENGTH, maxMessage: $this->translator->trans('validation.card.description_too_long', ['limit' => Constraints::CARD_DESCRIPTION_MAX_LENGTH])),
      ],
      'cardType' => [
        new Assert\NotBlank(message: $this->translator->trans('validation.card.card_type_not_blank')),
        new Assert\Choice(choices: Constants::FLASH_CARD_BAG_TYPES, message: $this->translator->trans('validation.card.card_type_invalid'))
      ],
      'cardColor' => [
        new Assert\CssColor(message: $this->translator->trans('validation.color.invalid_color')),
      ],
      'cardTextColor' => [
        new Assert\CssColor(message: $this->translator->trans('validation.color.invalid_color')),
      ]
    ];
    $error = ClassUtility::validateInputDTO($dto, $fields);

    if (count($error) > 0) {
      $flashBag->add('newCardError', $error);
      $flashBag->add('title', $dto->getTitle());
      $flashBag->add('subtitle', $dto->getSubtitle());
      $flashBag->add('description', $dto->getDescription());
      $flashBag->add('cardType', $dto->getCardType());
      $flashBag->add('cardColor', $dto->getCardColor());
      $flashBag->add('cardTextColor', $dto->getCardTextColor());
      return $this->redirect($previousRoute);
    }

    $this->service->addNewCard($dto);

    Utility::addNoticeToSessionFlash($this->session, 'success', $this->translator->trans('card_bag.new_card_created'));

    return $this->redirect($previousRoute);
  }

  #[Route(path: Routes::DELETE_OBJECT_ROUTE_URL, name: Routes::DELETE_OBJECT_ROUTE_NAME, methods: [Request::METHOD_POST])]
  public function deleteObject(Request $request)
  {
    // Get the previous route to redirect back to it
    $previousRoute = $request->headers->get('referer') ?? Routes::CARD_BAG_ROUTE_URL;

    $postData = $request->request->all();

    // Pass the form data to a DTO
    $dto = new SelectObjectDTO();
    ClassUtility::mapArrayToDTO($postData, $dto);

    $this->service->deleteObject($dto);

    Utility::addNoticeToSessionFlash($this->session, 'info', $this->translator->trans('card_bag.object_deleted'));

    return $this->redirect($previousRoute);
  }

  #[Route(path: Routes::UPDATE_CARD_ROUTE_URL, name: Routes::UPDATE_CARD_ROUTE_NAME, methods: [Request::METHOD_POST])]
  public function editCard(Request $request)
  {
    $flashBag = $this->getFlashBag();

    // Get the previous route to redirect back to it
    $previousRoute = $request->headers->get('referer') ?? Routes::CARD_BAG_ROUTE_URL;

    $postData = $request->request->all();

    // Pass the form data to a DTO
    $dto = new EditCardDTO();
    ClassUtility::mapArrayToDTO($postData, $dto);

    if ($this->service->getCard($dto->getCard()) === null) {
      Utility::addNoticeToSessionFlash($this->session, 'error', $this->translator->trans('card_bag.no_card_found'));
      return $this->redirect($previousRoute);
    }

    // Validate post data
    $fields = [
      'title' => [
        new Assert\NotBlank(message: $this->translator->trans('validation.card.title_not_blank')),
        new Assert\Length(max: Constraints::CARD_TITLE_MAX_LENGTH, maxMessage: $this->translator->trans('validation.card.title_too_long', ['limit' => Constraints::CARD_TITLE_MAX_LENGTH])),
      ],
      'subtitle' => [
        new Assert\Length(max: Constraints::CARD_SUBTITLE_MAX_LENGTH, maxMessage: $this->translator->trans('validation.card.subtitle_too_long', ['limit' => Constraints::CARD_SUBTITLE_MAX_LENGTH]))
      ],
      'description' => [
        new Assert\Length(max: Constraints::CARD_DESCRIPTION_MAX_LENGTH, maxMessage: $this->translator->trans('validation.card.description_too_long', ['limit' => Constraints::CARD_DESCRIPTION_MAX_LENGTH])),
      ],
      'cardType' => [
        new Assert\NotBlank(message: $this->translator->trans('validation.card.card_type_not_blank')),
        new Assert\Choice(choices: Constants::FLASH_CARD_BAG_TYPES, message: $this->translator->trans('validation.card.card_type_invalid'))
      ],
      'cardColor' => [
        new Assert\CssColor(message: $this->translator->trans('validation.color.invalid_color')),
      ],
      'cardTextColor' => [
        new Assert\CssColor(message: $this->translator->trans('validation.color.invalid_color')),
      ]
    ];
    $error = ClassUtility::validateInputDTO($dto, $fields);

    if (count($error) > 0) {
      $flashBag->add('editCardError', $error);
      $flashBag->add('card', $dto->getCard());
      $flashBag->add('title', $dto->getTitle());
      $flashBag->add('subtitle', $dto->getSubtitle());
      $flashBag->add('description', $dto->getDescription());
      $flashBag->add('cardType', $dto->getCardType());
      $flashBag->add('cardColor', $dto->getCardColor());
      $flashBag->add('cardTextColor', $dto->getCardTextColor());
      return $this->redirect($previousRoute);
    }

    $this->service->editCard($dto);

    Utility::addNoticeToSessionFlash($this->session, 'success', $this->translator->trans('card_bag.card_edit_success'));

    return $this->redirect($previousRoute);
  }

  /**
   * This function will focus on getting flash error data emitted from other functions in this controller
   * @return array
   */
  private function getErrorFlash(): array
  {
    $errors = [];

    $flashBag = $this->getFlashBag();

    // Get flash errors when creating new bag
    if ($flashBag->has('newBagError')) {
      $flashErrors = $flashBag->get('newBagError')[0];
      $flashNewBagName = $flashBag->get('newBagName')[0];
      $errors['newBagName'] = $flashNewBagName;
      $errors['newBagError'] = $flashErrors;
    }

    // Get flash errors when creating new card
    if ($flashBag->has('newCardError')) {
      $flashErrors = $flashBag->get('newCardError')[0];
      $flashTitle = $flashBag->get('title')[0];
      $flashSubtitle = $flashBag->get('subtitle')[0];
      $flashDescription = $flashBag->get('description')[0];
      $flashCardType = $flashBag->get('cardType')[0];
      $flashCardColor = $flashBag->get('cardColor')[0];
      $flashCardTextColor = $flashBag->get('cardTextColor')[0];
      $errors['newCardTitle'] = $flashTitle;
      $errors['newCardSubtitle'] = $flashSubtitle;
      $errors['newCardDescription'] = $flashDescription;
      $errors['newCardType'] = $flashCardType;
      $errors['newCardColor'] = $flashCardColor;
      $errors['newCardTextColor'] = $flashCardTextColor;
      $errors['newCardError'] = $flashErrors;
    }

    // Get flash errors when editing existing card
    if ($flashBag->has('editCardError')) {
      $flashErrors = $flashBag->get('editCardError')[0];
      $flashId = $flashBag->get('card')[0];
      $flashTitle = $flashBag->get('title')[0];
      $flashSubtitle = $flashBag->get('subtitle')[0];
      $flashDescription = $flashBag->get('description')[0];
      $flashCardType = $flashBag->get('cardType')[0];
      $flashCardColor = $flashBag->get('cardColor')[0];
      $flashCardTextColor = $flashBag->get('cardTextColor')[0];
      $errors['editCardId'] = $flashId;
      $errors['editCardTitle'] = $flashTitle;
      $errors['editCardSubtitle'] = $flashSubtitle;
      $errors['editCardDescription'] = $flashDescription;
      $errors['editCardType'] = $flashCardType;
      $errors['editCardColor'] = $flashCardColor;
      $errors['editCardTextColor'] = $flashCardTextColor;
      $errors['editCardError'] = $flashErrors;
    }

    return $errors;
  }
}
