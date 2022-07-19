<?php

namespace App\Http\Requests\API\Lead;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexRequest extends FormRequest
{
    /**
     * The route that users should be redirected to if validation fails.
     *
     * @var string
     */
    protected $redirectRoute = 'api.leads';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'format' => [Rule::in(['json', 'csv'])],
            'page' => ['integer'],
            'per_page' => ['integer'],
        ];
    }
}
