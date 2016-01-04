<?php

namespace Modules\Articles\Traits;

trait RepositoryTrait
{
    /**
     * Throw an exception from response
     */
    private function response_error($default_message = '')
    {
        if (!isset($this->repository)) {
            return null;
        }

        $response = isset($this->repository->response->error) ? $this->repository->response->error : null;
        $message = is_array($response) && isset($response['message']) ? $response['message'] : $response;
        empty($message) and $message = $default_message;

        throw new \Exception($message);
    }
}
