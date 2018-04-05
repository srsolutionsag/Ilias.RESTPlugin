# ILIAS REST Plugin

This is a plugin for the [ILIAS Learning Management System](<http://www.ilias.de>), which provides a customizable REST API.

## Warning
This is a modified version of the original [REST plugin](https://github.com/hrz-unimr/Ilias.RESTPlugin) 
which contains changes required by the Pegasus mobile application. Only use this
branch with the ILIAS Pegasus mobile application.

## Requirements
* Version: ILIAS 5.2 or 5.3
* PHP 5.5.9 or higher or PHP 7

## Installation

*   From within your ILIAS directory:

```bash
mkdir -p Customizing/global/plugins/Services/UIComponent/UserInterfaceHook
cd Customizing/global/plugins/Services/UIComponent/UserInterfaceHook
git clone https://github.com/hrz-unimr/Ilias.RESTPlugin.git REST
```

*   Open ILIAS Administration &gt; Plugins from the drop-down menu
*   Update and active REST-Plugin using the drop-down action-menu button

## Features:

*   Permission management for resources depending on REST clients using API-Keys
*   Full OAuth 2.0 support (see [RFC6749](<http://tools.ietf.org/html/rfc6749>)) including the grant types:
    *   Authorization Code
    *   Implicit
    *   Resource Owner Password Credentials
    *   Client Credentials
*   CRUD (Create-Read-Update-Delete) principle for resources
*   Easy integration of new REST endpoints possible
*   Based on the PHP SLIM Framework
*   Tools included (IShell, System Client, API Testing, IScenarios)

Note: Please refer to the [wiki](https://github.com/hrz-unimr/Ilias.RESTPlugin/wiki). pages for further information.

## Example
**Retrieve all available routes**

```bash
curl -X GET https://ilias.uni-marburg.de/Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/REST/api.php/v2/util/routes
```

More examples can be found in the [wiki](https://github.com/hrz-unimr/Ilias.RESTPlugin/wiki/Examples).

## License
This project is licensed under the GNU GPLv3 License - see the LICENSE.md file for details.
