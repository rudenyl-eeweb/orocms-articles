<?php
namespace Modules\Articles\Repositories;

use Modules\Articles\Entities\Article;
use OroCMS\Admin\Decorators\BootstrapTablePaginator;
use OroCMS\Admin\Contracts\EntityRepositoryInterface;

class ArticleRepository implements EntityRepositoryInterface
{
    public function getModel($with_trashed = false)
    {
        $model = Article::class;
        $model = new $model();

        return $with_trashed ? $model->withTrashed() : $model;
    }

    public function create(array $data)
    {
        return $this->getModel()->create($data);
    }
    
    public function delete($id, $force_delete = false)
    {
        $user = $this->findById($id);
        return $force_delete ? $user->forceDelete() : $user->delete();
    }

    public function perPage()
    {
        return config('articles.pages.article.perpage');
    }

    public function allWithTerm($context = null)
    {
        if (is_null($context)) {
            return $this->getAll();
        }

        return $this->search($context);
    }

    public function getAll()
    {
        return $this->paginate( $this->getModel(true)->latest() );
    }

    public function search($context)
    {
        $search = "%{$context}%";

        return $this->paginate(
            $this->getModel(true)->where('name', 'like', $search)->orWhere('email', 'like', $search) 
        );
    }

    public function findById($id)
    {
        return $this->getModel(true)->findorFail($id);
    }

    public function findBy($key, $value, $operator = '=')
    {
        return $this->paginate($this->getModel(true)->where($key, $operator, $value));
    }

    public function paginate($data)
    {
        $data = $data->paginate($this->perPage());

        return (new BootstrapTablePaginator($data))->render();
    }
}
