<?php

namespace App\Controller;

use App\Config\Constants;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\Service\Attribute\Required;

class BaseController extends AbstractController
{
  public TranslatorInterface $translator;
  public SessionInterface $session;
  public Response $unprocessableEntityResponse;

  #[Required]
  public function initProperties(TranslatorInterface $translator, RequestStack $requestStack)
  {
    $this->translator = $translator;
    $this->session = $requestStack->getSession();
    $this->unprocessableEntityResponse = new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY);
  }

  public function getFlashBag(): FlashBagInterface
  {
    if ($this->session instanceof FlashBagAwareSessionInterface) {
      return $this->session->getFlashBag();
    }

    throw new InternalErrorException(Constants::NO_SESSION_FLASH_BAG);
  }
}
