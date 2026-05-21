<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

class BaseApiController extends AbstractController
{
  public TranslatorInterface $translator;
  public SessionInterface $session;

  #[Required]
  public function initProperties(TranslatorInterface $translator, RequestStack $requestStack)
  {
    $this->translator = $translator;
    $this->session = $requestStack->getSession();
  }
}
