<?php

namespace App\Controller\Api\User;

use App\Config\ContentType;
use App\Config\Header;
use App\Config\Routes;
use App\Controller\Api\BaseApiController;
use App\Service\TopicService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TopicApiController extends BaseApiController
{
  public function __construct(private TopicService $topicService) {}

  #[Route(path: Routes::API_USER_TOPIC_ROUTE_URL, name: Routes::API_USER_TOPIC_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function getTopicList(Request $request): JsonResponse
  {
    $parentTopicId = $request->query->get('parentTopicId');
    $parentTopicId = $parentTopicId !== "" && $parentTopicId !== "null" ? (int)$parentTopicId : null;
    $topicList = $this->topicService->getTopicList($parentTopicId, false);

    return $this->json($topicList, Response::HTTP_OK, [
      Header::CONTENT_TYPE => ContentType::JSON
    ]);
  }
}
