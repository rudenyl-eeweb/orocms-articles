<?php 
namespace Modules\Articles\Providers;

use Illuminate\Support\ServiceProvider;

class ArticlesServiceProvider extends ServiceProvider 
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Boot the application events.
	 * 
	 * @return void
	 */
	public function boot()
	{
		$this->registerConfig();
		$this->registerTranslations();
		$this->registerViews();
		$this->registerComposers();
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{		
		//
	}

	/**
	 * Register config.
	 * 
	 * @return void
	 */
	protected function registerConfig()
	{
		$this->publishes([
		    __DIR__.'/../Config/config.php' => config_path('articles.php'),
		]);
		$this->mergeConfigFrom(
		    __DIR__.'/../Config/config.php', 'articles'
		);
	}

	/**
	 * Register views.
	 * 
	 * @return void
	 */
	public function registerViews()
	{
		$viewPath = base_path('resources/views/modules/articles');

		$sourcePath = __DIR__.'/../Resources/views';

		$this->publishes([
			$sourcePath => $viewPath
		]);

		$this->loadViewsFrom([$viewPath, $sourcePath], 'articles');
	}

	/**
	 * Register translations.
	 * 
	 * @return void
	 */
	public function registerTranslations()
	{
		$langPath = base_path('resources/lang/modules/articles');

		if (is_dir($langPath)) {
			$this->loadTranslationsFrom($langPath, 'articles');
		} 
		else {
			$this->loadTranslationsFrom(__DIR__ .'/../Resources/lang', 'articles');
		}
	}

	/**
	 * Register view composers.
	 * 
	 * @return void
	 */
	public function registerComposers()
	{
		view()->composer('articles::admin.form', function($view) {
		    $buffer = null;
		    if ($view->offsetExists('model')) {
		        $article = $view->offsetGet('model');

		        if ($results = event('articles.admin.onBeforeRenderItem', $article)) {
		            if (is_array($results)) {
		                $article = $results[0];
		            }
		        }

		        if ($results = event('articles.admin.onAfterRenderItem', $article)) {
		            $buffer = implode("\n", $results);
		        }
		    }

		    if ($buffer) {
		        $view->getFactory()->startSection('header', $buffer);
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
		return [];
	}

}
