<?php namespace Augneb\Captcha;

use Config;
use Illuminate\Support\ServiceProvider;

class CaptchaServiceProvider extends ServiceProvider 
{

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('augneb/captcha');

		$this->addValidatorExtends();
	}

	/**
	 * Extends Validator to include a recaptcha type
	 */
	public function addValidatorExtends()
	{
		$validator = $this->app['Validator'];
		
		$validator::extend('captcha', function($attribute, $value, $parameters)
		{
			return Captcha::check($value);
		});
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
	    $this->app['captcha'] = $this->app->share(function($app)
	    {
	        return Captcha::instance();
	    });

	    $this->app->booting(function()
	    {
			$aliases = Config::get('app.aliases');

			if (empty($aliases['Captcha']))
			{
				$loader = \Illuminate\Foundation\AliasLoader::getInstance();
				$loader->alias('Captcha','Augneb\Captcha\Facades\Captcha');
			}

		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('captcha');
	}

}