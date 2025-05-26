<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }

    /*
     * get custom messages for validation rules
     */
    public function messages(): array
    {
        return  [
            'name.required' => 'Nama brand harus diisi',
            'name.string' => 'Nama brand harus berupa string',
            'name.max' => 'Nama brand tidak boleh lebih dari 255 karakter',
            'description.string' => 'Deskripsi harus berupa string',
            'description.max' => 'Deskripsi tidak boleh lebih dari 1000 karakter',
            'logo.image' => 'Logo harus berupa gambar',
            'logo.mimes' => 'Logo harus berupa file dengan format jpeg, png, jpg, gif, atau svg',
            'logo.max' => 'Logo tidak boleh lebih dari 2MB',
        ];
    }
}
