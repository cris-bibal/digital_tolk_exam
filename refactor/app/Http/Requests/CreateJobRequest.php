<?php

namespace DTApi\Http\Requests;

use Illuminate\Http\Request;

class CreateJobRequest extends Request
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
        $userId = auth()->user()->user_id;

        if (auth()->user()->user_type != env('CUSTOMER_ROLE_ID')) return [];

        $rules = [
            'from_language_id'     => "required",
            'duration'     => "required",
        ];

        if($this->immediate == 'no') {
        	$rules['due_date'] = 'required';
        	$rules['due_time'] = 'required';
	        
	        if (!isset($this->customer_phone_type) && !isset($this->customer_physical_type)) {
	        	$rules['customer_phone_type'] = 'required';
	        }
        }


        return $rules;
    }

    public function messages()
    {
        return [
            'adid.unique' => 'ADID already registered.'
        ];
    }
}