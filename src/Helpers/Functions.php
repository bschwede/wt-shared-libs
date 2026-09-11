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
 * inspired by: https://github.com/Jefferson49/webtrees-common/blob/main/Helpers/Functions.php
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
 * Functions to be used in webtrees custom modules
 *
 */

declare(strict_types=1);

namespace Schwendinger\Webtrees\Helpers;

use Fig\Http\Message\RequestMethodInterface;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Enums\AccessLevel;
use Fisharebest\Webtrees\Fact;
use Fisharebest\Webtrees\GedcomRecord;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Schema\MigrationInterface;
use Fisharebest\Webtrees\Webtrees;
use Fisharebest\Webtrees\User;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Collection;

use Exception;
use PDOException;


/**
 * Functions to be used in webtrees custom modules
 */
class Functions
{

    /**
     * All users
     *
     * @return Collection<array-key,User>
     */
    public static function getAllUsers(): Collection
    {
        $query = DB::table('user')
        ->where('user.user_id', '>', '0')
        ->select([
            'user_id',
            'user_name',
            'real_name',
            'email',
        ]);

        return $query
            ->get()
            ->map(User::rowMapper());
    }

	/**
     * Get an array [name => title] for all trees, for which the current user is manager
     *
     * @param Collection $trees The trees, for which the list shall be generated
     *
     * @return array            error message
     */
    public static function getTreeNameTitleList(Collection $trees): array {

        $tree_list = [];

        foreach($trees as $tree) {
            if (Auth::isManager($tree)) {
                $tree_list[$tree->name()] = $tree->name() . ' (' . $tree->title() . ')';
            }
        }

        return $tree_list;
    }


    /**
     * Return the privatized GEDCOM of a Gedcom record
     *
     * @param GedcomRecord $record        Gedcom structure
     * @param int          $access_level  Access level of the user
     *
     * @return string
     */
    public static function getPrivatizedGedcom(GedcomRecord $record, int $access_level) : string {

        if (version_compare(Webtrees::VERSION, '2.3', '>=')) {
            return $record->privatizeGedcom(AccessLevel::from($access_level));
        }
        else {
            return $record->privatizeGedcom($access_level);
        }
    }

    /**
     * The facts and events for this record.
     *
     * @param GedcomRecord  $record
     * @param array<string> $filter
     * @param bool          $sort
     * @param ?int          $access_level
     * @param bool          $ignore_deleted
     *
     * @return Collection<int,Fact>
     */
    public static function getRecordFacts(
        GedcomRecord $record,
        array $filter = [],
        bool $sort = false,
        ?int $access_level = null,
        bool $ignore_deleted = false
    ): Collection {

        if (version_compare(Webtrees::VERSION, '2.3', '>=')) {
            return $record->facts($filter, $sort, AccessLevel::from($access_level), $ignore_deleted);
        }
        else {
            return $record->facts($filter, $sort, $access_level, $ignore_deleted);
        }
    }

    /**
     * Register a route
     *
     * @param string $path
     * @param string $name
     * @param        $handler
     * @param array  $middleware
     *
     * @return void
     */
    public static function registerRoute(string $path, string $name, $handler = null, array $middleware = []): void {

        $router = Registry::routeFactory()->routeMap();

        if (version_compare(Webtrees::VERSION, '2.3', '>=')) {

            $router->add($path, $name, $middleware);
            return;
        }
        else {
            $router
            ->get($name, $path, $handler)
            ->allows(RequestMethodInterface::METHOD_POST)
            ->extras(['middleware' => $middleware]);
            return;
        }
    }

    /**
     * Apply a module's Migration# class files (zero based) until
     * target_version - 1.
     *
     * Same approach as webtrees' Database::getSchema(), but using the module's
     * own preferences instead of the site settings. DDL runs outside the request
     * transaction (MySQL implicit commits), which is re-opened in the finally
     * block so webtrees' middleware can still commit.
     *
     * @param AbstractModule $module
     * @param string         $namespace      namespace of the Migration classes
     * @param string         $schema_name    preference name holding the current schema version
     * @param int            $target_version
     *
     * @return bool true if any update was applied
     */
    public static function updateSchema(AbstractModule $module, string $namespace, string $schema_name, int $target_version): bool
    {
        try {
            $current_version = intval($module->getPreference($schema_name));
        } catch (PDOException $ex) {
            // During initial installation the site tables won't exist yet.
            $current_version = 0;
        }

        $updates_applied = false;

        $connection = DB::schema()->getConnection();

        if ($connection->transactionLevel() > 0) {
            $connection->commit();
        }

        try {
            // Update the schema, one version at a time.
            while ($current_version < $target_version) {
                $class = $namespace . '\\Migration' . $current_version;
                /** @var MigrationInterface $migration */
                $migration = new $class();
                $migration->upgrade();
                $current_version++;

                // The module row may not exist yet on first install (e.g. called
                // from setName()), so only persist the version once it does.
                if (DB::table('module')->where('module_name', '=', $module->name())->exists()) {
                    $module->setPreference($schema_name, (string) $current_version);
                }
                $updates_applied = true;
            }
        } finally {
            // Re-open a transaction for webtrees' middleware to commit, even if
            // the DDL above failed.
            $connection->beginTransaction();
        }

        return $updates_applied;
    }
}