<?php

namespace Modules\Articles\Http\Controllers;

use ACPClient\RESTClient;
use Modules\Articles\Traits\RepositoryTrait;
use OroCMS\Admin\Controllers\BaseController;

class ArticlesController extends BaseController
{
    use RepositoryTrait;

    protected $route_prefix = 'articles';
    protected $view_prefix = 'articles';
    protected $theme = '';

    protected $repository;

    /**
     * @var string
     */
    protected $auth_token;

    /**
     * @param ACPClient\RESTClient $repository
     */
    function __construct(RESTClient $repository)
    {
        $this->repository = $repository;

        // authenticate
        $this->repository->authenticate();

        // get token
        if (!isset($this->repository->response->token)) {
            $this->response_error();
        }
        $this->auth_token = $this->repository->response->token ?: null;

        // set authorization header
        $this->repository->headers([
            'Authorization: Bearer ' . $this->auth_token
        ]);
    }

    public function index()
    {
        //
    }

    public function show($id)
    {
        $this->repository->get('/articles/' . $id);

        try {
            $article = (object)$this->repository->response->article ?: null;
        }
        catch(\Exception $e) {
            $this->response_error( $e->getMessage() );
        }

        $view = $this->view('index', compact('article', 'base_layout'));

        #
        # onAfterRenderItem
        #
        event('articles.onAfterRenderItem', $view);

        return $view;
    }
}
