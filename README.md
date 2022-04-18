# gifmaker
Makes gif from mp4

## Instalation
* **PHP version:** 8.0+
* **Composer:** `composer require mashoshin/gifmaker`

## Usage
```php
use GifMaker\GifMaker;
use GifMaker\ValueObject\GifSettings;

$saveDir = 'path/to/saveDir';

// Your custom settings
$settings = new GifSettings(imageDelay: 20);

$gifMaker = new GifMaker($saveDir);

$gifMaker->setSettings($settings);

$source = 'path/to/sourceMp4';

$gifMaker->create($source);
```

