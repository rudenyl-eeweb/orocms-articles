<?php 
namespace Modules\Articles\Http\Controllers;

use Modules\Articles\repositories\ArticleRepository;
use OroCMS\Admin\Controllers\BaseController;

class ArticlesController extends BaseController 
{
    protected $route_prefix = 'articles';
    protected $view_prefix = 'articles';

    protected $repository;

    function __construct(ArticleRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Article $article)
    {
        //
    }

    public function show($slug)
    {
        $article = $this->repository->findBy('slug', $slug)->first();

        $view = $this->view('index', compact('article'));

        # 
        # onAfterRenderItem
        #
        event('articles.onAfterRenderItem', $view);

        return $view;
    }
}