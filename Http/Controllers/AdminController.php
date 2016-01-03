<?php 
namespace Modules\Articles\Http\Controllers;

use ACPClient\RESTClient;
use OroCMS\Admin\Controllers\BaseController;
use Modules\Articles\Validation\Create;
use Modules\Articles\Validation\Update;
use Modules\Articles\Events\ArticleEventHandler;
use Illuminate\Http\Request;

class AdminController extends BaseController 
{
    protected $route_prefix = 'admin.modules';
    protected $view_prefix = 'articles';
    protected $theme = '';

    /**
     * @var string
     */
    protected $auth_token;

    /**
     * @param Modules\Articles\Repositories\ArticleRepository $repository
     */
    function __construct(RESTClient $repository) 
    {
        $this->repository = $repository;

        // authenticate
        $this->repository->authenticate();

        $this->auth_token = $this->repository->response->get('token');
    }

    public function index(Request $request)
    {
        $data = $this->repository->get('/articles', [
            'headers' => [
                'Authorization: Bearer ' . $this->auth_token
            ]
        ]);

        $articles = $this->repository->response->all();

        if ($request->isJson()) {
            return response()->json($articles);
        }

        return $this->view('admin.index');
    }

    public function create() {}
    public function store() {}
    public function edit() {}
    public function update() {}
    public function destroy() {}
}