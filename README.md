# php-draftjs-html
Export DraftJS ContentState to HTML from PHP

## Installation
```sh
$ docker-compose build
$ docker-compose composer
```

## Usage
```php
<?php

namespace Tests;

use Willtj\PhpDraftjsHtml\Converter;
use Prezly\DraftPhp\Converter as DraftConverter;

// From a JSON string
$contentState = DraftConverter::convertFromJson($input);
$converter = new Converter;
$result = $converter
    ->setState($contentState)
    ->toHtml();
```

Basic customisation can be carried out by overriding the default style map, eg
```php
$converter->updateStyleMap(['BOLD' => ['element' => 'b']]);
```

The class can be extended for more advanced custom rendering.

## Tests
```sh
$ docker-compose phpunit
```
