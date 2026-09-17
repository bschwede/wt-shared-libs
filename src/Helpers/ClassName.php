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
 * inspired by: https://github.com/Jefferson49/webtrees-common/blob/main/Helpers/ClassName.php
 * Copyright (C) 2026 Markus Hemprich
 *                    <http://www.familienforschung-hemprich.de>
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
class ClassName
{
    public const CONTROL_PANEL       = 'ControlPanel';
    public const HOME_PAGE           = 'HomePage';
    public const LOGOUT_PAGE         = 'LogoutPage';
    public const EDIT_NOTE_ACTION    = 'EditNoteAction';
    public const HELP_TEXT           = 'HelpText';
    public const SELECT_LANGUAGE     = 'SelectLanguage';
    public const TREE_PAGE           = 'TreePage';
    public const TREE_PAGE_BLOCK_EDIT = 'TreePageBlockEdit';
    public const USER_PAGE_BLOCK_EDIT = 'UserPageBlockEdit';
    public const EXCEPTION_HTTP_FORBIDDEN = 'HttpForbiddenException';


    private const CLASS_NAMES = [
        self::CONTROL_PANEL => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\ControlPanel::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\ControlPanel::class,
        ],
        self::HOME_PAGE => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\HomePage::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\HomePage::class,
        ],
        self::LOGOUT_PAGE => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\Logout::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\Logout::class,
        ],
        self::EDIT_NOTE_ACTION => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\EditNoteAction::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\EditNote::class,
        ],
        self::HELP_TEXT => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\HelpText::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\HelpText::class,
        ],
        self::SELECT_LANGUAGE => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\SelectLanguage::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\SelectLanguage::class,
        ],
        self::TREE_PAGE => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\TreePage::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\TreePage::class,
        ],
        self::TREE_PAGE_BLOCK_EDIT => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\TreePageBlockEdit::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\TreePageBlockEdit::class,
        ],
        self::USER_PAGE_BLOCK_EDIT => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\UserPageBlockEdit::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\UserPageBlockEdit::class,
        ],
        self::EXCEPTION_HTTP_FORBIDDEN => [
            '2.1' => \Fisharebest\Webtrees\Http\Exceptions\HttpAccessDeniedException::class,
            '2.3' => \Fisharebest\Webtrees\Http\Exceptions\HttpForbiddenException::class,
        ],        
    ];

    /**
     * Get the class name (depending on the webtrees version)
     *
     * @param  string $name
     *
     * @return string
     */
    public static function get(string $name) : string {

        if (Functions::wtIsAtLeast2_3()) {
            return self::CLASS_NAMES[$name]['2.3'] ?? '';
        } else {
            return self::CLASS_NAMES[$name]['2.1'] ?? '';
        }
    }

    public static function isInstanceOf(object $instance, string $name): bool
    {
        $class = self::get($name);
        return ($class !== '' && class_exists($class) && $instance instanceof $class);
    }

}