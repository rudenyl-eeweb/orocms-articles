<?php

namespace Modules\Articles\Http\Controllers;

use ACPClient\RESTClient;
use OroCMS\Admin\Controllers\BaseController;
use Modules\Articles\Traits\ResponseRepositoryTrait;
use Modules\Articles\Events\ArticleEventHandler;
use Illuminate\Http\Request;

class AdminController extends BaseController
{
    use ResponseRepositoryTrait;

    protected $route_prefix = 'admin.modules';
    protected $view_prefix = 'articles';
    protected $theme = '';

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

    public function index(Request $request)
    {
        $this->repository->get('/articles', [
            'parameters' => $request->only('sort', 'order', 'limit', 'offset')
        ]);
        $articles = $this->repository->response ?: [];

        if ($request->isJson()) {
            return response()->json($articles);
        }

        return $this->view('admin.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return $this->view('admin.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $this->repository->post('/articles', [
            'parameters' => $request->all()
        ]);

        if ($error_msg = $this->repository->get_last_error()) {
            \Log::error($error_msg);

            if (isset($this->error->code)) {
                //
                // Form request validation error (EXCEPTION_VALIDATION)
                //
                if ($this->error->code == 28) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors( json_decode($this->error->message) );
                }
            }

            abort(501, $error_msg);
        }

        return $this->redirect('articles.index')
            ->withFlashMessage( trans('articles::articles.admin.message.create.success') )->withFlashType('info');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $this->repository->get('/articles/' . $id);

        if ($error_msg = $this->repository->get_last_error()) {
            \Log::error($error_msg);

            $error_code = in_array($this->error->code, [404,500,501]) ? $this->error->code : 404;

            abort($error_code, $error_msg);
        }

        $article = (object)$this->repository->response->article ?: null;

        return $this->view('admin.edit', compact('article'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function update(Request $request, $id)
    {
        try {
            if ($request->ajax()) {
                $success = false;
                $message = trans('articles::articles.admin.message.restored');

                if ($request->has('restore')) {
                    // update article
                    $this->repository->put('/articles/' . $id, [
                        'parameters' => [
                            'restore' => true
                        ]
                    ]);

                    if ($error_msg = $this->repository->get_last_error()) {
                        abort(501, $error_msg);
                    }
                }

                return response()->json(compact('success', 'message'));
            }

            // update article
            $this->repository->put('/articles/' . $id, [
                'parameters' => $request->all()
            ]);

            if ($error_msg = $this->repository->get_last_error()) {
                if (isset($this->error->code)) {
                    //
                    // Form request validation error (EXCEPTION_VALIDATION)
                    //
                    if ($this->error->code == 28) {
                        return redirect()->back()
                            ->withInput()
                            ->withErrors( json_decode($this->error->message) );
                    }
                }

                abort(501, $error_msg);
            }

            return $this->redirect('articles.index')
                ->withFlashMessage( trans('articles::articles.admin.message.update.success') )->withFlashType('info');
        }
        catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }

            return $this->redirect('articles.index')
                ->withFlashMessage($e->getMessage())->withFlashType('danger');
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function destroy(Request $request, $id=null)
    {
        try {
            // prioritize input over slug
            $cids = $request->get('id');
            if (empty($cids)) {
                $cids = [$id];
            }

            // force delete?
            $force_delete = $request->has('force_delete') and (int)$request->has('force_delete');

            $deleted = 0;
            $errors = [];

            foreach ($cids as $id) {
                $this->repository->delete('/articles/' . $id, [
                    'parameters' => [
                        'force' => $force_delete
                    ]
                ]);

                if ($error_msg = $this->repository->get_last_error()) {
                    $errors[] = sprinf('(%d) %s', $id, $error_msg);
                }
                else {
                    $tag = ($force_delete ? '' : 'marked_') . 'deleted';
                    $success = $this->repository->response->$tag ?: false;
                    if ($success) {
                        $deleted++;
                    }
                }
            }

            if (empty($deleted)) {
                throw new \Exception( implode("\n", $errors) );
            }

            $tag = $force_delete ? 'success' : 'marked';
            $message = trans('articles::articles.admin.message.delete.' . $tag);
            if ($deleted && $deleted < count($cids)) {
                $message = trans('articles::articles.admin.message.partial.delete.' . $tag);
            }
        }
        catch (\Exception $e) {
            $message = $e->getMessage();
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }

        return $this->redirect('admin.index');
    }
}
