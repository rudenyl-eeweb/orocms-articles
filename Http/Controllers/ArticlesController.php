<?php

namespace Modules\Articles\Http\Controllers;

use ACPClient\RESTClient;
use Modules\Articles\Traits\ResponseRepositoryTrait;
use OroCMS\Admin\Controllers\BaseController;

class ArticlesController extends BaseController
{
    use ResponseRepositoryTrait;

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
        if ($this->repository->connected()) {
            $this->auth_token = $this->repository->response->token ?: null;
        }
        else {
            \Log::error('REST API connect error: ' . ($this->repository->get_last_error() ?: 'Unknown error has occured') );
        }

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

        if ($error_msg = $this->repository->get_last_error()) {
            \Log::error($error_msg);

            $error_code = in_array($this->error->code, [404,500,501]) ? $this->error->code : 404;

            abort($error_code, $error_msg);
        }

        $article = (object)$this->repository->response->article ?: null;

        $view = $this->view('index', compact('article'));

        #
        # Fore onAfterRenderItem
        #
        event('articles.onAfterRenderItem', $view);

        return $view;
    }
}
