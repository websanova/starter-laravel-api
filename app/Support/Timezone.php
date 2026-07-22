<?php

namespace App\Support;

use DateTimeZone;
use Symfony\Component\Intl\Timezones;
use Symfony\Component\Intl\Exception\MissingResourceException;

class Timezone
{
    /**
     * Return the master list of supported IANA timezone identifiers.
     */
    public static function identifiers(): array
    {
        static $identifiers;

        return $identifiers ??= DateTimeZone::listIdentifiers();
    }

    /**
     * Return the identifiers as a value/label set for client selection,
     * with the city portion localized to the given locale.
     */
    public static function options(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        static $options = [];

        if (isset($options[$locale])) {
            return $options[$locale];
        }

        return array_map(fn ($tz) => [
            'value' => $tz,
            'label' => self::label($tz, $locale),
        ], self::identifiers());
    }

    /**
     * Build a localized "Region / City" label for the given identifier.
     */
    protected static function label(string $tz, string $locale): string
    {
        [$region, $city] = array_pad(explode('/', $tz, 2), 2, '');

        try {
            $name = Timezones::getName($tz, $locale);
        } catch (MissingResourceException $e) {
            $name = '';
        }

        if (str_contains($name, '(')) {
            $city = rtrim(substr($name, strpos($name, '(') + 1), ')');
        } else {
            $city = str_replace('_', ' ', $city);
        }

        $region = __("timezone.regions.{$region}", [], $locale);

        return $city === '' ? $region : $region.' / '.$city;
    }
}
