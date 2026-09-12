<?php

/**
 * bschwede/wt-shared-libs: Library to share common code between webtrees custom modules
 * 
 * webtrees: online genealogy
 * Copyright (C) 2026 webtrees development team
 *                    <http://webtrees.net>
 *
 * Copyright (C) 2026 Bernd Schwendinger
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 *
 * Services for class names (depending on the webtrees version)
 *
 */

declare(strict_types=1);

namespace Schwendinger\Webtrees\Helpers;

use Fisharebest\Webtrees\Webtrees;


/**
 * Services for class names (depending on the webtrees version)
 */
class ExceptionName
{
    public const HTTP_FORBIDDEN       = 'HttpForbiddenException';


    private const CLASS_NAMES = [
        self::HTTP_FORBIDDEN => [
            '2.1' =>  \Fisharebest\Webtrees\Http\Exceptions\HttpAccessDeniedException::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Exceptions\HttpForbiddenException::class,
        ],
    ];

    /**
     * Get the class name (depending on the webtrees version)
     *
     * @param  string $name
     *
     * @return string
     */
    public static function get(string $name, string $fallback = "\\Exception") : string {

        if (version_compare(Webtrees::VERSION, '2.3', '>=')) {
            return self::CLASS_NAMES[$name]['2.3'] ?? $fallback;
        } else {
            return self::CLASS_NAMES[$name]['2.1'] ?? $fallback;
        }
    }

    public static function isInstanceOf(\Throwable $exception, string $name): bool
    {
        $class = self::get($name);        
        return ($class !== '' && class_exists($class) && $exception instanceof $class);
    }

}