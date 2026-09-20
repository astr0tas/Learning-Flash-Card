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

    $topicList = $this->service->getTopicList(null);
    $cardList = $this->service->getCardList(null);
    $breadcrumb = [['icon' => $this->renderView('icons/trash.svg'), 'label' => $this->translator->trans('menu.trash'), 'url' => Routes::TRASH_ROUTE_URL]];

    $this->service->enableSoftDeleteFilter();

    return $this->render(view: TwigTemplate::PAGE_USER_TRASH, parameters: [
      'topicList' => $topicList,
      'cardList' => $cardList,
      'breadcrumb' => $breadcrumb
    ]);
  }

  #[Route(path: Routes::TRASH_TOPIC_ROUTE_URL, name: Routes::TRASH_TOPIC_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function getTopicDetail(int $id)
  {
    $this->service->disableSoftDeleteFilter();

    $topic = $this->service->getTopic($id);

    if ($topic === null) {
      throw $this->createNotFoundException($this->translator->trans('trash.topic_not_found'));
    }

    $cards = $topic->getCardEntities();
    $childrenTopics = $topic->getChildrenTopicEntities();
    $topicTree = $this->service->getTopicTree($id);

    $cards->initialize();
    $childrenTopics->initialize();

    $this->service->enableSoftDeleteFilter();

    // Convert the topic tree to breadcrumbs array
    $breadcrumb = [['icon' => $this->renderView('icons/trash.svg'), 'label' => $this->translator->trans('menu.trash'), 'url' => Routes::TRASH_ROUTE_URL]];
    $breadcrumb = $this->service->parseTopicTreeToBreadcrumb($topicTree, $breadcrumb);

    return $this->render(view: TwigTemplate::PAGE_USER_TRASH, parameters: [
      'topicList' => $childrenTopics,
      'cardList' => $cards,
      'topic' => $topic,
      'breadcrumb' => $breadcrumb
    ]);
  }

  #[Route(path: Routes::PERMANENT_DELETE_OBJECT_ROUTE_URL, name: Routes::PERMANENT_DELETE_OBJECT_ROUTE_NAME, methods: [Request::METHOD_POST])]
  public function deleteObjectPermanet(Request $request)
  {
    // Get the previous route to redirect back to it
    $previousRoute = $request->headers->get('referer') ?? Routes::TOPIC_ROUTE_URL;

    // Handle login submission
    $postData = $request->request->all();

    // Pass the form data to a DTO
    $dto = new SelectObjectDTO();
    ClassUtility::mapArrayToDTO($postData, $dto);

    $this->service->deleteObjectPermanet($dto);

    Utility::addNoticeToSessionFlash($this->session, 'info', $this->translator->trans('trash.permanent_delete_success'));

    return $this->redirect($previousRoute);
  }

  #[Route(path: Routes::RESTORE_OBJECT_ROUTE_URL, name: Routes::RESTORE_OBJECT_ROUTE_NAME, methods: [Request::METHOD_POST])]
  public function restoreObject(Request $request)
  {
    // Get the previous route to redirect back to it
    $previousRoute = $request->headers->get('referer') ?? Routes::TOPIC_ROUTE_URL;

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
