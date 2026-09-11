# webtrees shared libs for cutom modules

[Description](#description) | [Requirements](#requirements) | [Installation](#installation) | [License](#license)

[![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](http://www.gnu.org/licenses/gpl-3.0)


> [!IMPORTANT]
> **Primary Repository:** <https://codeberg.org/bschwede/wt-shared-libs>
>
> **All activity happens on Codeberg:**
>
> - ✅ Issues → [Open on Codeberg](https://codeberg.org/bschwede/wt-shared-libs/issues)
> - ✅ Pull Requests → [Open on Codeberg](https://codeberg.org/bschwede/wt-shared-libs/pulls)
>
> Mirror Repository: <https://github.com/bschwede/wt-shared-libs>

<a name="description"></a>
## Description
This package bundles shared classes and code. It is intended for use in bschwede's webtrees custom modules.

Parts and especially the loading routine are inspired by the similar module [Jefferson49/webtrees-common](https://github.com/Jefferson49/webtrees-common)


<a name="requirements"></a>
## Requirements

This module requires **webtrees** version 2.2/2.3.
This module has the same requirements as [webtrees#system-requirements](https://github.com/fisharebest/webtrees#system-requirements).


<a name="installation"></a>
## Installation
The consuming module simply needs to include a `require_once` for the `autoload.php` file contained here in its own loader routine. It's `composer.json` should include the following content:

```json
    "repositories": [
        { "type": "path", "url": "../wt-shared-libs", "options": { "symlink": false } }
    ],
    "require": {
        "bschwede/wt-shared-libs": "^1.0"
    }
```


<a name="license"></a>
## License

* Copyright (C) 2026 Bernd Schwendinger
* Derived from **webtrees** - Copyright 2025 webtrees development team.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.

* * *
