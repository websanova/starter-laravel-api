<?php

namespace App\Support;

use DateTimeZone;

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
     * Return the identifiers as a value/label set for client selection.
     */
    public static function options(): array
    {
        static $options;

        return $options ??= array_map(fn ($tz) => [
            'value' => $tz,
            'label' => str_replace('_', ' ', $tz),
        ], self::identifiers());
    }
}
