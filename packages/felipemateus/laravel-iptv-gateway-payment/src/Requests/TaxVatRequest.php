<?php

namespace FelipeMateus\IPTVGatewayPayment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaxVatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'string|required',
            'porcent' => 'required|numeric|min:0|max:100',
            'active' => "boolean"
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'A Name is required',
            'porcent.required' => 'A Porcent is required',
            'porcent.numeric' => 'A porcet need be a number',
            'porcent.min' => 'A Porcent min is 0',
            'porcent.max' => 'A Porcent max is 100',

        ];
    }
}
