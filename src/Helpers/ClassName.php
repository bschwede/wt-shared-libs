<?php

/**
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
    public const ACCOUNT_DELETE      = 'AccountDelete';
    public const ACCOUNT_EDIT        = 'AccountEdit';
    public const DATA_FIX_PAGE       = 'DataFixPage';
    public const CONTROL_PANEL       = 'ControlPanel';
    public const COPY_FACT           = 'CopyFact';
    public const DELETE_FACT         = 'DeleteFact';
    public const EDIT_FACT           = 'EditFact';
    public const FAMILY_PAGE         = 'FamilyPage';
    public const HOME_PAGE           = 'HomePage';
    public const INDIVIDUAL_PAGE     = 'IndividualPage';
    public const LOGIN_ACTION        = 'LoginAction';
    public const LOGIN_PAGE          = 'LoginPage';
    public const LOGOUT_PAGE         = 'LogoutPage';
    public const MEDIA_PAGE          = 'MediaPage';
    public const NOTE_PAGE           = 'NotePage';
    public const PASSWORD_REQUEST    = 'PasswordRequest';
    public const PASSWORD_RESET      = 'PasswordReset';
    public const PENDING_CHANGES     = 'PendingChanges';
    public const REGISTER            = 'RegisterPage';
    public const REPOSITORY_PAGE     = 'RepositoryPage';
    public const SOURCE_PAGE         = 'SourcePage';
    public const SUBMITTER_PAGE      = 'SubmitterPage';
    public const UPGRADE_WIZARD_PAGE = 'UpgradeWizardPage';


/*
linkenhancer
use Fisharebest\Webtrees\Http\RequestHandlers\TreePageBlockEdit;
use Fisharebest\Webtrees\Http\RequestHandlers\UserPageBlockEdit;
use Fisharebest\Webtrees\Http\RequestHandlers\HomePage;
use Fisharebest\Webtrees\Http\RequestHandlers\TreePage;
use Fisharebest\Webtrees\Http\RequestHandlers\Logout;
use Fisharebest\Webtrees\Http\RequestHandlers\SelectLanguage;
use Fisharebest\Webtrees\Http\RequestHandlers\HelpText;

cronjob
RouteEventService::fallbackMap als key (String)
Fisharebest\Webtrees\Http\RequestHandlers\EditNoteAction
*/

    private const CLASS_NAMES = [
        self::ACCOUNT_DELETE => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\AccountDelete::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\Account::class,
        ],
        self::ACCOUNT_EDIT => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\AccountEdit::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\Account::class,
        ],
        self::CONTROL_PANEL => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\ControlPanel::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\ControlPanel::class,
        ],
        self::COPY_FACT => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\CopyFact::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\CopyFact::class,
        ],
        self::DATA_FIX_PAGE => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\DataFixPage::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\DataFixPage::class,
        ],
        self::DELETE_FACT => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\DeleteFact::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\DeleteFact::class,
        ],
        self::EDIT_FACT => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\EditFactPage::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\EditFact::class,
        ],
        self::FAMILY_PAGE => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\FamilyPage::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\FamilyPage::class,
        ],
        self::HOME_PAGE => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\HomePage::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\HomePage::class,
        ],
        self::INDIVIDUAL_PAGE => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\IndividualPage::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\IndividualPage::class,
        ],
        self::LOGIN_ACTION => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\LoginAction::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\Login::class,
        ],
        self::LOGIN_PAGE => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\LoginPage::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\Login::class,
        ],
        self::LOGOUT_PAGE => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\Logout::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\Logout::class,
        ],
        self::MEDIA_PAGE => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\MediaPage::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\MediaPage::class,
        ],
        self::NOTE_PAGE => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\NotePage::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\NotePage::class,
        ],
        self::PASSWORD_REQUEST => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\PasswordRequestPage::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\PasswordRequest::class,
        ],
        self::PASSWORD_RESET => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\PasswordResetPage::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\PasswordReset::class,
        ],
        self::PENDING_CHANGES => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\PendingChanges::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\PendingChanges::class,
        ],
        self::REPOSITORY_PAGE => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\RepositoryPage::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\RepositoryPage::class,
        ],
        self::REGISTER => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\RegisterPage::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\Register::class,
        ],
        self::SOURCE_PAGE => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\SourcePage::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\SourcePage::class,
        ],
        self::SUBMITTER_PAGE => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\SubmitterPage::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\SubmitterPage::class,
        ],
        self::UPGRADE_WIZARD_PAGE => [
            '2.1' =>  \Fisharebest\Webtrees\Http\RequestHandlers\UpgradeWizardPage::class,
            '2.3' =>  \Fisharebest\Webtrees\Http\Controllers\UpgradeWizardPage::class,
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

        if (version_compare(Webtrees::VERSION, '2.3', '>=')) {
            return self::CLASS_NAMES[$name]['2.3'] ?? '';
        } else {
            return self::CLASS_NAMES[$name]['2.1'] ?? '';
        }
    }
}