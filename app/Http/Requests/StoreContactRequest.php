<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:contacts,email'],
            'phone' => ['nullable', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'Imię jest wymagane.',
            'first_name.max' => 'Imię nie może być dłuższe niż 255 znaków.',
            'last_name.required' => 'Nazwisko jest wymagane.',
            'last_name.max' => 'Nazwisko nie może być dłuższe niż 255 znaków.',
            'email.required' => 'Adres email jest wymagany.',
            'email.email' => 'Adres email musi być prawidłowy.',
            'email.unique' => 'Kontakt z tym adresem email już istnieje.',
            'email.max' => 'Adres email nie może być dłuższy niż 255 znaków.',
            'phone.max' => 'Numer telefonu nie może być dłuższy niż 255 znaków.',
            'company.required' => 'Nazwa firmy jest wymagana.',
            'company.max' => 'Nazwa firmy nie może być dłuższa niż 255 znaków.',
            'position.required' => 'Stanowisko jest wymagane.',
            'position.max' => 'Stanowisko nie może być dłuższe niż 255 znaków.',
            'tags.array' => 'Tagi muszą być w formacie tablicy.',
            'tags.*.exists' => 'Wybrany tag nie istnieje.',
        ];
    }
}