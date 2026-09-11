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
 * inspired by: https://github.com/Jefferson49/webtrees-common/blob/main/autoload.php
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
 * Autoload the library for common code between webtrees custom modules
 * 
 */

$libname = 'bschwede/wt-shared-libs';

$search = str_replace('/', DIRECTORY_SEPARATOR,'/' . $libname);
$dir    = str_replace($search,  '', __DIR__);
$loader = new Composer\Autoload\ClassLoader($dir);

try {
    $autoload_common_library_version = Composer\InstalledVersions::getVersion($libname);
}
catch (\OutOfBoundsException $e) {
    $autoload_common_library_version = '';
}

$local_composer_versions = require $dir . '/composer/installed.php';
$local_common_library_version = $local_composer_versions['versions'][$libname]['version'];

//If the found library is later than the current autoload version, prepend the found library to autoload
//This ensures that always the latest library version is autoloaded
if (version_compare($local_common_library_version, $autoload_common_library_version, '>')) {
    $ns_prefix = 'Schwendinger\\Webtrees\\';
    foreach (['Helpers'] as $path) {
        $loader->addPsr4($ns_prefix . $path . '\\', __DIR__ . '/src/' . $path);
    }
    $loader->register(true);
}