<?php

namespace FelipeMateus\IPTVCustomers\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IPTVCustomerInvoceCreateInvoceRequest extends FormRequest
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
            'duedate_at' => 'required|date',
            'payeddate_at'=>'nullable|date'
        ];
    }
}
