<?php
namespace Modules\Articles\Validation;

use OroCMS\Admin\Validation\Validator;

class Create extends Validator
{
    public function rules()
    {
        return [
            'title' => 'required|min:5',
            'slug' => 'min:3|unique:articles,slug',
            'description' => 'required'
        ];
    }
}
