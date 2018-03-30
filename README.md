stats-table-bundle
==================

Add symfony wrapper to use igraal/stats-table as controller returned value.

All you have to do is to add a route with a .xls, .json or .csv extension and then add the `@StatsTableResult` annotation to your controller's action. It will automatically detect the extension of your route and convert the `StatsTable` into a file with the correct format.

For more information on the `StatsTable` usage, please read [StatsTable documentation](https://github.com/igraal/stats-table#stats-table)

## Installation

This bundle requires and is based on [Sensio Framework Extra Bundle](https://github.com/sensiolabs/SensioFrameworkExtraBundle).

### Using composer

Add igraal/stats-table-bundle to your requirements :

```json
{
    "require": {
        ...,
        "igraal/stats-table-bundle": "*"
    }
}
```

### Additional packages

Also add `phpoffice/phpexcel` for .xls file support.

### Declare the bundle

Edit your `AppKernel.php` file to add the bundle :

```php
    $bundles = [
        ...,
        new IgraalOSB\StatsTableBundle\IgraalOSBStatsTableBundle(),
    ];
```

## Usage

All you have to do is add the `@StatsTableResult` annotation to your controller's annotations.

This is a sample Controller file :

```php
// Controller/MyController.php

use IgraalOSB\StatsTableBundle\Configuration\StatsTableResult;

class MyController extends BaseController
{
    /**
     * @Route("/stats-table-test.{_format}", requirements={"_format": "json|xls|csv"})
     * @StatsTableResult
     */
    public function statsTableTestAction()
    {
        return new \IgraalOSL\StatsTable\StatsTable(
            [[1, 1], [2, 3]],
            ['One', 'Two']
        );
    }
}
```
