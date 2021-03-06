# Thinkphp Captcha for Laravel 4

A simple [Laravel 4](http://four.laravel.com/) service provider for including the [Thinkphp Captcha for Laravel 4](https://github.com/augneb/captcha).

## Preview
![Preview](http://demo.tudou.li/captcha/demo.jpg)

## Installation

The Captcha Service Provider can be installed via [Composer](http://getcomposer.org) by requiring the
`augneb/captcha` package and setting the `minimum-stability` to `dev` (required for Laravel 4) in your
project's `composer.json`.

```json
{
    "require": {
        "laravel/framework": "4.2.*",
        "augneb/captcha": "dev-master"
    },
    "minimum-stability": "dev"
}
```

Update your packages with ```composer update``` or install with ```composer install```.

## Usage

To use the Captcha Service Provider, you must register the provider when bootstrapping your Laravel application. There are
essentially two ways to do this.

Find the `providers` key in `app/config/app.php` and register the Captcha Service Provider.

```php
    'providers' => array(
        // ...
        'Augneb\Captcha\CaptchaServiceProvider',
    )
```

## Configuration

To use your own settings, publish config.

```$ php artisan config:publish augneb/captcha```

## Example Usage

```php

    // [your site path]/app/routes.php

    Route::any('/captcha-test', function()
    {

        if (Request::getMethod() == 'POST')
        {
            $rules =  array('captcha' => array('required', 'captcha'));
            $validator = Validator::make(Input::all(), $rules);
            if ($validator->fails())
            {
                echo '<p style="color: #ff0000;">Incorrect!</p>';
            }
            else
            {
                echo '<p style="color: #00ff30;">Matched :)</p>';
            }
        }

        $content = Form::open(array(URL::to(Request::segment(1))));
        $content .= '<p>' . HTML::image('/captcha-test', 'Captcha image') . '</p>';
        $content .= '<p>' . Form::text('captcha') . '</p>';
        $content .= '<p>' . Form::submit('Check') . '</p>';
        $content .= Form::close();
        return $content;

    });
```

^_^

## Links

* [L4 Captcha on Github](https://github.com/augneb/captcha)
* [L4 Captcha on Packagist](https://packagist.org/packages/augneb/captcha)
* [License](http://www.opensource.org/licenses/mit-license.php)
* [Laravel website](http://laravel.com)
* [Laravel Turkiye website](http://www.laravel.gen.tr)
