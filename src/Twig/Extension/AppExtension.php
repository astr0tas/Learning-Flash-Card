<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\AppExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Config\Constants;

class AppExtension extends AbstractExtension
{
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
            new TwigFunction('get_locale', [$this, 'getLocaleFromCookie']),
        ];
    }

    public function isDarkMode(): bool
    {
        if (empty($_COOKIE[Constants::COOKIES['dark_mode']])) {
            return false;
        }

        return \boolval($_COOKIE[Constants::COOKIES['dark_mode']]) ?? false;
    }

    public function getLocaleFromCookie(): string
    {
        if (empty($_COOKIE[Constants::COOKIES['locale']])) {
            return Constants::DEFAULT_LOCALE;
        }

        return \strval($_COOKIE[Constants::COOKIES['locale']]) ?? Constants::DEFAULT_LOCALE;
    }
}
