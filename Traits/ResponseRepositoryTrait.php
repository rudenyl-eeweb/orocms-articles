<?php

namespace Modules\Articles\Traits;

trait ResponseRepositoryTrait
{
    /**
     * Process getter
     */
    function __get($key)
    {
        if ($key == 'error') {
            if (!isset($this->repository)) {
                return null;
            }

            return isset($this->repository->response->error) ? (object)$this->repository->response->error : null;
        }

        return isset($this->$key) ? $this->key : null;
    }
}
