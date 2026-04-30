<?php

namespace App\Controller\User;

use App\Config\Routes;
use App\Config\TwigTemplate;
use App\Controller\BaseController;
use App\DTO\SelectObjectDTO;
use App\Service\TrashService;
use App\Utility\ClassUtility;
use App\Utility\Utility;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class TrashController extends BaseController
{
  public function __construct(private TrashService $service) {}

  #[Route(path: Routes::TRASH_ROUTE_URL, name: Routes::TRASH_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function index()
  {
    $this->service->disableSoftDeleteFilter();

    $bagList = $this->service->getBagList(null);
    $cardList = $this->service->getCardList(null);

    $this->service->enableSoftDeleteFilter();

    return $this->render(view: TwigTemplate::PAGE_USER_TRASH, parameters: [
      'bagList' => $bagList,
      'cardList' => $cardList
    ]);
  }

  #[Route(path: Routes::TRASH_BAG_ROUTE_URL, name: Routes::TRASH_BAG_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function getBagDetail(int $id)
  {
    $this->service->disableSoftDeleteFilter();

    $bag = $this->service->getBag($id);

    if ($bag === null) {
      throw $this->createNotFoundException($this->translator->trans('trash.bag_not_found'));
    }

    $cards = $bag->getCardEntities();
    $childrenBags = $bag->getChildrenCardBagEntities();
    $bagTree = $this->service->getBagTree($id);

    $cards->initialize();
    $childrenBags->initialize();

    $this->service->enableSoftDeleteFilter();

    // Convert the bag tree to breadcrumbs array
    $breadcrumb = [['icon' => $this->renderView('icons/folder.svg'), 'label' => $this->translator->trans('menu.trash'), 'url' => Routes::TRASH_ROUTE_URL]];
    $breadcrumb = $this->service->parseBagTreeToBreadcrumb($bagTree, $breadcrumb);

    return $this->render(view: TwigTemplate::PAGE_USER_TRASH, parameters: [
      'bagList' => $childrenBags,
      'cardList' => $cards,
      'bag' => $bag,
      'breadcrumb' => $breadcrumb
    ]);
  }

  #[Route(path: Routes::PERMANENT_DELETE_OBJECT_ROUTE_URL, name: Routes::PERMANENT_DELETE_OBJECT_ROUTE_NAME, methods: [Request::METHOD_GET, Request::METHOD_POST])]
  public function deleteObjectPermanet(Request $request)
  {
    // Get the previous route to redirect back to it
    $previousRoute = $request->headers->get('referer') ?? Routes::CARD_BAG_ROUTE_URL;

    if ($request->getMethod() === Request::METHOD_GET) {
      return $this->redirect($previousRoute);
    }

    // Handle login submission
    $postData = $request->request->all();

    // Pass the form data to a DTO
    $dto = new SelectObjectDTO();
    ClassUtility::mapArrayToDTO($postData, $dto);

    $this->service->deleteObjectPermanet($dto);

    Utility::addNoticeToSessionFlash($this->session, 'info', $this->translator->trans('trash.permanent_delete_success'));

    return $this->redirect($previousRoute);
  }

  #[Route(path: Routes::RESTORE_OBJECT_ROUTE_URL, name: Routes::RESTORE_OBJECT_ROUTE_NAME, methods: [Request::METHOD_GET, Request::METHOD_POST])]
  public function restoreObject(Request $request)
  {
    // Get the previous route to redirect back to it
    $previousRoute = $request->headers->get('referer') ?? Routes::CARD_BAG_ROUTE_URL;

    if ($request->getMethod() === Request::METHOD_GET) {
      return $this->redirect($previousRoute);
    }

    // Handle login submission
    $postData = $request->request->all();

    // Pass the form data to a DTO
    $dto = new SelectObjectDTO();
    ClassUtility::mapArrayToDTO($postData, $dto);

    $this->service->restoreObject($dto);

    Utility::addNoticeToSessionFlash($this->session, 'success', $this->translator->trans('trash.restore_success'));

    return $this->redirect($previousRoute);
  }
}
