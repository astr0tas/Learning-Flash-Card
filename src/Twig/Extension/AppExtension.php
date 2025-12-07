<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\AppExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Config\Constants;
use App\Service\BaseService;

class AppExtension extends AbstractExtension
{
    public function __construct(
        private BaseService $baseService
    ) {}

    // public function getFilters(): array
    // {
    //     return [
    //         // If your filter generates SAFE HTML, you should add a third
    //         // parameter: ['is_safe' => ['html']]
    //         // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
    //         new TwigFilter('filter_name', [AppExtensionRuntime::class, 'doSomething']),
    //     ];
    // }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_dark_mode', [$this, 'isDarkMode']),
            new TwigFunction('get_locale', [$this->baseService, 'getLocaleFromCookie']),
        ];
    }

    public function isDarkMode(): bool
    {
        if (empty($_COOKIE[Constants::DARK_MODE_COOKIE_NAME])) {
            return false;
        }

        return \boolval($_COOKIE[Constants::DARK_MODE_COOKIE_NAME]) ?? false;
    }
}
