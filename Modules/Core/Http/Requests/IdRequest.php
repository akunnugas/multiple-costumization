<?php

namespace Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class IdRequest extends FormRequest
{
    public $value;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $id = array_values($this->route()->parameters());
        $pop = array_pop($id);

        $this->merge([
            'id' => $pop,
        ]);
    }

    public function passedValidation() {
        $this->value = (int) $this->id;

        unset($this['id']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'numeric'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ModelNotFoundException();
    }
}
