<?php

namespace Appoly\MailWeb\Facades;

use RuntimeException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Facade;
use Illuminate\Contracts\Support\Htmlable;

class MailWeb extends Facade
{
    protected $css = [__DIR__ . '/../dist/mailweb.css'];

    protected static function getFacadeAccessor()
    {
        return 'MailWeb';
    }

    public function css(string|Htmlable|array|null $css = null): string|self
    {
        if (func_num_args() === 1) {
            $this->css = array_values(array_unique(array_merge($this->css, Arr::wrap($css)), SORT_REGULAR));

            return $this;
        }

        return collect($this->css)->reduce(function ($carry, $css) {
            if ($css instanceof Htmlable) {
                return $carry . Str::finish($css->toHtml(), PHP_EOL);
            } else {
                if (($contents = @file_get_contents($css)) === false) {
                    throw new RuntimeException("Unable to load Pulse dashboard CSS path [$css].");
                }

                return $carry . "<style>{$contents}</style>" . PHP_EOL;
            }
        }, '');
    }
}
