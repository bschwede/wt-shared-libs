<?php

/**
 * webtrees: online genealogy
 * Copyright (C) 2026 webtrees development team
 *                    <http://webtrees.net>
 *
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
 */

declare(strict_types=1);

namespace Schwendinger\Webtrees\Traits;

use Fisharebest\Webtrees\Module\ModuleCustomTrait as WebtreesModuleCustomTrait;
use Fisharebest\Webtrees\Webtrees;

use LogicException;


/**
 * Trait ModuleCustomTrait - certain default implementations of ModuleCustomInterface
 * 
 * Consuming classes must define the following constants:
 * CUSTOM_AUTHOR, CUSTOM_VERSION, CUSTOM_LAST, CUSTOM_WEBSITE
 *
 */
trait ModuleCustomTrait 
{
    use WebtreesModuleCustomTrait;


    /**
     * {@inheritDoc}
     *
     * @return string
     *
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleAuthorName()
     */
    public function customModuleAuthorName(): string
    {
        return self::moduleCustomConstant('CUSTOM_AUTHOR');
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     *
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleVersion()
     */
    public function customModuleVersion(): string
    {
        return self::moduleCustomConstant('CUSTOM_VERSION');
    }

    /**
     * A URL that will provide the latest version of this module.
     *
     * @return string
     */
    public function customModuleLatestVersionUrl(): string
    {
        return self::moduleCustomConstant('CUSTOM_LAST');
    }

    /**
     * Where to get support for this module.  Perhaps a github repository?
     *
     * @return string
     */
    public function customModuleSupportUrl(): string
    {
        return self::moduleCustomConstant('CUSTOM_WEBSITE');
    }

    /**
     * Additional/updated translations.
     *
     * @param string $language
     *
     * @return array<string>
     */
    public function customTranslations(string $language): array
    {
        $file_base = $this->resourcesFolder() . 'lang' . DIRECTORY_SEPARATOR . $language;
        $file = null;
        foreach (['.php', '.po'] as $ext) {
            if (is_readable($file_base . $ext)) {
                $file = $file_base . $ext;
                break;
            }
        }

        // webtrees 2.2 still provides the former file-based localization package.
        if (class_exists('\\Fisharebest\\Localization\\Translation')) {
            return $file ? (new \Fisharebest\Localization\Translation($file))->asArray() : [];
        }

        // webtrees 2.3 replaced fisharebest/localization with its own stream-based loader.
        if (class_exists('\\Fisharebest\\Webtrees\\I18N\\Translation')) {
            if (str_ends_with($file, '.po')) {
                $stream = fopen($file, 'rb');

                if ($stream === false) {
                    return [];
                }

                try {
                    $translation = \Fisharebest\Webtrees\I18N\Translation::fromPoStream($stream);

                    return $translation->toArray();
                } finally {
                    fclose($stream);
                }

            } else {
                $translation = \Fisharebest\Webtrees\I18N\Translation::fromPhpFile($file);

                return $translation->toArray();
            }
            
        }        
        return [];
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     *
     * @see \Fisharebest\Webtrees\Module\AbstractModule::resourcesFolder()
     */
    public function resourcesFolder(): string
    {
        // __DIR__ inside a trait resolves to the trait file, not the consuming
        // class. The consuming module class lives at <modul>/src/<X>Module.php,
        // so its resources/ dir (<modul>/resources/) is two levels up.
        $classFile = (new \ReflectionClass($this))->getFileName();
        return dirname($classFile, 2) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR;
    }

    /**
     * Get the module folder
     *
     * @return string
     */
    public function moduleFolder(): string
    {
        $folder = $this->name();
        $folder = substr($this->name(), 1);
        $folder = substr($folder, 0, strlen($folder) - 1);
        return Webtrees::MODULES_DIR . $folder;
    }


    /**
     * Retrieve a required class constant from the concrete module class.
     */
    private static function moduleCustomConstant(string $name): string
    {
        $class = static::class;
        $constant = $class . '::' . $name;

        if (!defined($constant)) {
            throw new LogicException(sprintf('Missing required constant %s in class %s', $name, $class));
        }

        return constant($constant);
    }  
}