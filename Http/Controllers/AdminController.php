<?php
namespace Modules\Articles\Http\Controllers;

use OroCMS\Admin\Controllers\BaseController;
use Modules\Articles\Repositories\ArticleRepository;
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
     * @var Modules\Articles\Entities\Article
     */
    protected $articles;

    /**
     * @param Modules\Articles\Repositories\ArticleRepository $repository
     */
    function __construct(ArticleRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        $articles = $this->repository->allWithTerm($request->get('q'));

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
        $data = $request->all();
        $data['slug'] = $this->repository->getModel()->toSlug($data['slug']);

        $article = $this->repository->create($data);

        return $this->redirect('articles.index')
            ->withFlashMessage('Article successfully created.')->withFlashType('info');
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
            $article = $this->repository->findById($id);

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
            // get article
            $article = $this->repository->findById($id);

            if ($request->ajax()) {
                $success = false;
                $message = null;

                if ($request->has('restore')) {
                    // restore
                    $article->restore();

                    $success = true;
                    $message = trans('articles::articles.admin.message.restored');
                }

                return response()->json(compact('success', 'message'));
            }

            $data = $request->all();
            $data['slug'] = $article->toSlug($data['slug']);

            $article->update($data);

            return $this->redirect('articles.index')
                ->withFlashMessage( trans('articles::articles.admin.message.update.success') )->withFlashType('info');
        }
        catch (ModelNotFoundException $e) {
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

            foreach ($cids as $id) {
                $this->repository->delete($id, $force_delete);
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => trans('articles::articles.admin.user.message.delete.' . ($force_delete ? 'success' : 'marked'))
                ]);
            }

            return $this->redirect('admin.index');
        }
        catch (ModelNotFoundException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }

            return $this->redirect('admin.index');
        }
    }
}
