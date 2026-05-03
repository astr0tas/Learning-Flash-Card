<?php

namespace App\Controller\Api\User;

use App\Config\ContentType;
use App\Config\Header;
use App\Config\Routes;
use App\Controller\Api\BaseApiController;
use App\Service\CardBagService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CardBagApiController extends BaseApiController
{
  public function __construct(private CardBagService $cardBagService) {}

  #[Route(path: Routes::API_USER_CARD_BAG_ROUTE_URL, name: Routes::API_USER_CARD_BAG_ROUTE_NAME, methods: [Request::METHOD_GET])]
  public function getCardList(Request $request): JsonResponse
  {
    $parentBagId = $request->query->get('parentBagId') ?: null;
    $cardList = $this->cardBagService->getBagList($parentBagId);

    return $this->json($cardList, Response::HTTP_OK, [
      Header::CONTENT_TYPE => ContentType::JSON
    ]);
  }
}
