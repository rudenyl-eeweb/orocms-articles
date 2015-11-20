<?php
namespace Modules\Articles\Validation;

use OroCMS\Admin\Validation\Validator;

class Update extends Validator
{
    public function rules()
    {
        if (!$this->beforeActivateRule()) {
            return [];
        }

        // get id from segment
        $id = $this->segment(4);

        $rules = [
            'title' => 'required|min:5',
            'slug' => 'min:3|unique:articles,slug,'.$id,
            'description' => 'required'
        ];

        return $rules;
    }
}
