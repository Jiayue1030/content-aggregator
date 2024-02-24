<?php return array (
  'aws/aws-sdk-php-laravel' => 
  array (
    'providers' => 
    array (
      0 => 'Aws\\Laravel\\AwsServiceProvider',
    ),
    'aliases' => 
    array (
      'AWS' => 'Aws\\Laravel\\AwsFacade',
    ),
  ),
  'barryvdh/laravel-dompdf' => 
  array (
    'providers' => 
    array (
      0 => 'Barryvdh\\DomPDF\\ServiceProvider',
    ),
    'aliases' => 
    array (
      'Pdf' => 'Barryvdh\\DomPDF\\Facade\\Pdf',
      'PDF' => 'Barryvdh\\DomPDF\\Facade\\Pdf',
    ),
  ),
  'laravel/sail' => 
  array (
    'providers' => 
    array (
      0 => 'Laravel\\Sail\\SailServiceProvider',
    ),
  ),
  'laravel/sanctum' => 
  array (
    'providers' => 
    array (
      0 => 'Laravel\\Sanctum\\SanctumServiceProvider',
    ),
  ),
  'laravel/tinker' => 
  array (
    'providers' => 
    array (
      0 => 'Laravel\\Tinker\\TinkerServiceProvider',
    ),
  ),
  'nesbot/carbon' => 
  array (
    'providers' => 
    array (
      0 => 'Carbon\\Laravel\\ServiceProvider',
    ),
  ),
  'nunomaduro/collision' => 
  array (
    'providers' => 
    array (
      0 => 'NunoMaduro\\Collision\\Adapters\\Laravel\\CollisionServiceProvider',
    ),
  ),
  'nunomaduro/termwind' => 
  array (
    'providers' => 
    array (
      0 => 'Termwind\\Laravel\\TermwindServiceProvider',
    ),
  ),
  'rap2hpoutre/laravel-log-viewer' => 
  array (
    'providers' => 
    array (
      0 => 'Rap2hpoutre\\LaravelLogViewer\\LaravelLogViewerServiceProvider',
    ),
  ),
  'roach-php/laravel' => 
  array (
    'providers' => 
    array (
      0 => 'RoachPHP\\Laravel\\RoachServiceProvider',
    ),
  ),
  'robertogallea/laravel-python' => 
  array (
    'providers' => 
    array (
      0 => 'robertogallea\\LaravelPython\\ServiceProvider',
    ),
    'aliases' => 
    array (
      'Python' => 'robertogallea\\LaravelPython\\Facades\\PythonFacade',
    ),
  ),
  'spatie/laravel-ignition' => 
  array (
    'providers' => 
    array (
      0 => 'Spatie\\LaravelIgnition\\IgnitionServiceProvider',
    ),
    'aliases' => 
    array (
      'Flare' => 'Spatie\\LaravelIgnition\\Facades\\Flare',
    ),
  ),
  'vatttan/apdf' => 
  array (
    'providers' => 
    array (
      0 => 'Vatttan\\Apdf\\ApdfServiceProvider',
    ),
  ),
  'vedmant/laravel-feed-reader' => 
  array (
    'providers' => 
    array (
      0 => 'Vedmant\\FeedReader\\FeedReaderServiceProvider',
    ),
    'aliases' => 
    array (
      'FeedReader' => 'Vedmant\\FeedReader\\Facades\\FeedReader',
    ),
  ),
  'weidner/goutte' => 
  array (
    'providers' => 
    array (
      0 => 'Weidner\\Goutte\\GoutteServiceProvider',
    ),
    'aliases' => 
    array (
      'Goutte' => 'Weidner\\Goutte\\GoutteFacade',
    ),
  ),
);