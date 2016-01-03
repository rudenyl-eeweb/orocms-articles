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
		$this->registerTranslations();
		$this->registerViews();
		$this->registerComposers(); 
		$this->registerListeners(); 
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerConfig();
	}

	/**
	 * Register config.
	 * 
	 * @return void
	 */
	protected function registerConfig()
	{
		$configPath = __DIR__.'/../Config/config.php';
        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, 'modules.configs.articles');
        }
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
		    # 
		    # onBeforeRenderItem
		    #
		    if ($view->offsetExists('model')) {
		        $article = $view->offsetGet('model');

		        event('articles.admin.onBeforeRenderItem', $article);
		    }

		    # 
		    # onAfterRenderItem
		    #
	        event('articles.admin.onAfterRenderItem', $view);
		});
	}

	/**
	 * Register listeners.
	 * 
	 * @return void
	 */
	public function registerListeners()
	{
		\Event::listen('composing: articles::index', function($view) {
			if (is_array($view->snippets)) {
        		$view->getFactory()->startSection('content', '@parent' . implode("\n", $view->snippets));
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
