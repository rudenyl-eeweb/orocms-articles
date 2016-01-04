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

    public function index(Request $request)
    {
        $this->repository->get('/articles');
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
    public function store(Create $request)
    {
        $this->repository->post('/articles', [
            'parameters' => $request->all()
        ]);

        $success = $this->repository->response->created ?: false;
        if (!$success) {
            $this->response_error( trans('articles::articles.admin.message.create.failed') );
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
        try {
            $this->repository->get('/articles/' . $id);
            $article = (object)$this->repository->response->article ?: null;

            return $this->view('admin.edit', compact('article'));
        }
        catch (ModelNotFoundException $e) {
            return $this->view('admin.index');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     *
     * @return Response
     */
    public function update(Update $request, $id)
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

                    $success = $this->repository->response->restored ?: false;
                    if (!$success) {
                        $this->response_error( trans('articles::articles.admin.message.restore.failed') );
                    }
                }

                return response()->json(compact('success', 'message'));
            }

            // update article
            $this->repository->put('/articles/' . $id, [
                'parameters' => $request->all()
            ]);

            $success = $this->repository->response->updated ?: false;
            if (!$success) {
                $this->response_error( trans('articles::articles.admin.error.unknown') );
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
            foreach ($cids as $id) {
                $this->repository->delete('/articles/' . $id, [
                    'parameters' => [
                        'force' => $force_delete
                    ]
                ]);

                $tag = ($force_delete ? '' : 'marked_') . 'deleted';
                $success = $this->repository->response->$tag ?: false;
                if ($success) {
                    $deleted++;
                }
            }

            if (empty($deleted)) {
                throw new \Exception( trans('articles::articles.admin.message.delete.failed') );
            }

            $tag = $force_delete ? 'success' : 'marked';
            $message = trans('articles::articles.admin.message.delete.' . $tag);
            if ($deleted && $deleted < count($cids)) {
                $message = trans('articles::articles.admin.message.partial.delete.' . $tag);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }

            return $this->redirect('admin.index');
        }
        catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }

            return $this->redirect('admin.index');
        }
    }

    /**
     * Throw an exception from response
     */
    private function response_error($default_message = '')
    {
        $response = isset($this->repository->response->error) ? $this->repository->response->error : null;
        $message = is_array($response) && isset($response['message']) ? $response['message'] : $response;
        empty($message) and $message = $default_message;

        throw new \Exception($message);
    }
}
