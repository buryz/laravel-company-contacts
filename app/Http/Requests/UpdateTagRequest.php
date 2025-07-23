<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTagRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tags')->where(function ($query) {
                    return $query->where('created_by', auth()->id());
                })->ignore($this->tag),
            ],
            'color' => [
                'required',
                'string',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nazwa tagu jest wymagana.',
            'name.string' => 'Nazwa tagu musi być tekstem.',
            'name.max' => 'Nazwa tagu nie może być dłuższa niż 255 znaków.',
            'name.unique' => 'Tag o tej nazwie już istnieje.',
            'color.required' => 'Kolor tagu jest wymagany.',
            'color.regex' => 'Kolor musi być w formacie hex (np. #FF0000).',
        ];
    }
}