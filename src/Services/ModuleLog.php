<?php

/*
 * bschwede/wt-shared-libs: Library to share common code between webtrees custom modules
 *
 * Copyright (C) 2026 Bernd Schwendinger
 *
 * webtrees: online genealogy application
 * Copyright (C) 2026 webtrees development team.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Schwendinger\Webtrees\Services;

use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\Log;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Validator;
use Psr\Http\Message\ServerRequestInterface;

use function error_log;

/**
 * Per-module logger with 3 severity levels and optional user-visible flash.
 *
 * Usage:
 *   ModuleLog::for('my_module')->error('something broke', 'Context');
 *
 * Levels:
 *   error() — always logged (error_log + webtrees wt_log table), optional flash
 *   debug() — only when ?debug=1 (like in Webtrees core Log class) or module pref is on (error_log only)
 *   trace() — only when module pref is on (error_log only, admin-sensitive)
 */
final class ModuleLog
{
    private const MAX_FLASH_PER_REQUEST = 5;

    /** @var array<string, self> */
    private static array $instances = [];

    private int $flash_count = 0;

    private function __construct(
        private readonly string $module_name,
        private readonly bool $pref_enabled = false
    ) {}

    /**
     * Get or create the instance for a given module.
     * First call sets the config; subsequent calls return the same instance.
     */
    public static function for(string $module_name, bool $pref_enabled = false): self
    {
        return self::$instances[$module_name] ??= new self($module_name, $pref_enabled);
    }

    /** @internal For testing only. */
    public static function reset(): void
    {
        self::$instances = [];
    }

    /**
     * Always logged — for unexpected errors.
     * Goes to PHP error_log AND webtrees log table.
     *
     * @param string      $message      Technical log message (may contain exception text)
     * @param string|null $context      Sub-context for filtering (e.g. "WthbService")
     * @param string|null $flash        If set: user-facing translated message for flash banner
     * @param string      $flash_status Bootstrap alert type: 'danger'|'warning'|'info'
     */
    public function error(string $message, ?string $context = null, ?string $flash = null, string $flash_status = 'danger'): void
    {
        $prefix = $this->prefix($context, 'err');
        error_log($prefix . $message);

        try {
            Log::addErrorLog($prefix . $message);
        } catch (\Throwable) {
            // log table may not exist during very early boot
        }

        if ($flash !== null && $this->canFlash()) {
            FlashMessages::addMessage($flash, $flash_status);
            $this->flash_count++;
        }
    }

    /**
     * Low-sensitivity debug (counts, timings, state flags).
     * Enabled by: ?debug=1 OR module pref.
     *
     * IMPORTANT: Never include user data, file paths, or GEDCOM content.
     * Allowed: counts, flags, timings, mode names.
     */
    public function debug(string $message, ?string $context = null): void
    {
        if (!$this->isDebugLevel1()) {
            return;
        }
        error_log($this->prefix($context, 'dbg') . $message);
    }

    /**
     * Sensitive debug (paths, config values, XREFs, stack traces).
     * Enabled ONLY by module preference (admin). NOT by ?debug=1.
     */
    public function trace(string $message, ?string $context = null): void
    {
        if (!$this->pref_enabled) {
            return;
        }
        error_log($this->prefix($context, 'trace') . $message);
    }

    /**
     * Whether debug-level logging is active.
     */
    public function isDebugEnabled(): bool
    {
        return $this->isDebugLevel1();
    }

    // ─── Private ────────────────────────────────────────────────────────────

    private function isDebugLevel1(): bool
    {
        if ($this->pref_enabled) {
            return true;
        }
        if (Registry::container()->has(ServerRequestInterface::class)) {
            return Validator::attributes(
                Registry::container()->get(ServerRequestInterface::class)
            )->boolean('debug', false);
        }
        return false;
    }

    private function canFlash(): bool
    {
        if (PHP_SAPI === 'cli') {
            return false;
        }
        if ($this->flash_count >= self::MAX_FLASH_PER_REQUEST) {
            return false;
        }
        return Registry::container()->has(ServerRequestInterface::class);
    }

    private function prefix(?string $context, string $level): string
    {
        $ctx = $context !== null ? ":{$context}" : '';
        return "[{$this->module_name}:{$level}{$ctx}] ";
    }
}
