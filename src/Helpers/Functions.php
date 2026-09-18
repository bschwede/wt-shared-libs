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
use Psr\Http\Message\ServerRequestInterface;

use Exception;
use PDOException;


/**
 * Functions to be used in webtrees custom modules
 */
class Functions
{

    /**
     * Stem aliases for genuine 2.2.6→2.3 stem renamings (re-merges) that the
     * suffix stripper does not resolve. Keys/values are stems (after namespace and suffix stripping).
     * Idempotent. GET-relevant cases: default-blocks, data-fix, my-account.
     */
    private const array HANDLER_KEY_ALIASES = [
        'TreePageDefaultEdit' => 'TreePageDefault',
        'DataFixChoose'       => 'DataFix',
        'AccountEdit'         => 'Account',
    ];

    /**
     * Is the running webtrees version >= the given minimum version?
     * Webtrees::VERSION is a compile-time constant, so the result is stable
     * for the process; the check is also trivially cheap.
     *
     * @param string $minVersion e.g. '2.3' or '2.2.5'
     *
     * @return bool
     */
    public static function webtreesAtLeast(string $minVersion): bool
    {
        return version_compare(Webtrees::VERSION, $minVersion, '>=');
    }

    /**
     * webtrees >= 2.3 (the 2.2.6/2.3 code-split gate).
     *
     * @return bool
     */
    public static function wtIsAtLeast2_3(): bool
    {
        return self::webtreesAtLeast('2.3');
    }

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

        if (self::wtIsAtLeast2_3()) {
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

        if (self::wtIsAtLeast2_3()) {
            return $record->facts($filter, $sort, AccessLevel::from($access_level), $ignore_deleted);
        }
        else {
            return $record->facts($filter, $sort, $access_level, $ignore_deleted);
        }
    }

    /**
     * Register a route.
     *
     * $name MUST be the handler FQCN: it becomes the route name (2.2.6) / controller
     * (2.3), which route() URL generation and dispatch rely on. $path is the full path.
     *
     * @param string             $path
     * @param string             $name       handler FQCN (route name)
     * @param string|object|null $handler    2.2.6 only (Aura route handler)
     * @param list<class-string> $middleware
     * @param bool               $allow_post 2.2.6 only; 2.3 routes have no per-route methods
     *
     * @return void
     */
    public static function registerRoute(string $path, string $name, $handler = null, array $middleware = [], bool $allow_post = false): void
    {
        $router = Registry::routeFactory()->routeMap();

        if (self::wtIsAtLeast2_3()) {
            // 2.3: RouteCollection::add($url, $controller, $middleware) — name = $name
            $router->add($path, $name, $middleware);

            return;
        }

        // 2.2.6: Aura\Router\Map
        $route = $router->get($name, $path, $handler);

        if ($allow_post) {
            $route->allows(RequestMethodInterface::METHOD_POST);
        }

        $route->extras(['middleware' => $middleware]);
    }

    /**
     * Normalize a route object to a version-independent array.
     *
     * @param object $route 2.2.6 Aura\Router\Route or 2.3 \Fisharebest\Webtrees\Http\Routing\Route
     *
     * @return array{path: string, handler: string, method: string, extras: string, attr: array}
     */
    public static function describeRoute(object $route): array
    {
        if (self::wtIsAtLeast2_3()) {
            return [
                'path'    => $route->url,
                'handler' => $route->controller,
                'method'  => '',
                'extras'  => implode('|', (array) $route->middleware),
                'attr'    => [],
            ];
        }

        return [
            'path'    => $route->path,
            'handler' => $route->name,
            'method'  => implode('|', (array) $route->allows),
            'extras'  => is_array($route->extras) && isset($route->extras['middleware']) ? implode('|', $route->extras['middleware']) : '',
            'attr'    => (array) $route->attributes,
        ];
    }

    /**
     * Version-neutral, canonical form of a route handler (for `route_help_map.handler_key`).
     *  - Core HTTP FQCN (2.2.6 RequestHandlers\ / 2.3 Controllers\):
     *      Remove namespace + suffix (Page|Action|Modal) + apply alias map;
     *      ModuleAction remains 1:1 (version-stable, name unchanged).
     *  - Everything else (module FQCN, module/point names, custom FQCN): unchanged.
     *
     * @return string
     */
    public static function canonicalHandlerKey(string $handler): string
    {
        $h = trim($handler);
        if ($h !== ''
            && (str_starts_with($h, 'Fisharebest\Webtrees\Http\RequestHandlers\\')
                || str_starts_with($h, 'Fisharebest\Webtrees\Http\Controllers\\'))) {
            $parts = explode('\\', $h);
            $base = (string) $parts[count($parts) - 1];

            // ModuleAction is version-stable (remains in RequestHandlers) -> 1:1, no suffix stripping.
            if ($base === 'ModuleAction') {
                return $h;
            }

            foreach (['Page', 'Action', 'Modal'] as $sfx) {
                if (str_ends_with($base, $sfx) && strlen($base) > strlen($sfx)) {
                    $base = substr($base, 0, -strlen($sfx));
                }
            }

            return self::HANDLER_KEY_ALIASES[$base] ?? $base;
        }

        return $h;
    }

    /**
     * All registered routes (version-independent accessor).
     *
     * @return object[]
     */
    public static function allRoutes(): array
    {
        $router = Registry::routeFactory()->routeMap();

        return self::wtIsAtLeast2_3() ? $router->all() : $router->getRoutes();
    }

    /**
     * The matched route parameters as a flat map of JSON-encodable scalars
     * (objects such as the Tree object are omitted).
     *
     *  - 2.2.6 (Aura): the scalars of the route's `attributes`.
     *  - 2.3:          the scalars read from the request for every token in the
     *                  route URL (the Router middleware puts them there).
     *
     * The token regex also matches optional segments `{/x}` (e.g. `{/fact_id}`).
     *
     * @param object $route 2.2.6 Aura\Router\Route or 2.3 \Fisharebest\Webtrees\Http\Routing\Route
     *
     * @return array<string, string|int|float|bool>
     */
    public static function routeParams(object $route, ServerRequestInterface $request): array
    {
        $attrs = self::wtIsAtLeast2_3()
            ? self::routeParams23($route, $request)
            : (array) ($route->attributes ?? []);

        $out = [];
        foreach ($attrs as $key => $value) {
            if (is_string($value) || is_int($value) || is_float($value) || is_bool($value)) {
                $out[(string) $key] = $value;
            }
        }

        return $out;
    }

    /**
     * 2.3 only: matched route tokens sourced from the request (set by the
     * Router middleware), objects such as the Tree object omitted.
     *
     * @return array<string, mixed>
     */
    private static function routeParams23(object $route, ServerRequestInterface $request): array
    {
        $attrs = [];
        if (preg_match_all('/\{\/?([a-zA-Z_]\w*)\}/', (string) $route->url, $m)) {
            foreach ($m[1] as $token) {
                $value = $request->getAttribute($token);
                if ($value !== null && !is_object($value)) {
                    $attrs[$token] = $value;
                }
            }
        }

        return $attrs;
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
            try {
                $connection->commit();
            } catch (PDOException $ex) { // suppress "PDOException: There is no active transaction" caused by other module updates
            }
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