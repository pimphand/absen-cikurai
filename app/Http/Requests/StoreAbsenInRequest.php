<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAbsenInRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'latitude_check_in' => 'required',
            'longitude_check_in' => 'required',
            'photo_check_in' => 'required|image|mimes:jpeg,png,jpg,gif',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'latitude_check_in.required' => 'latitude tidk boleh kosong',
            'longitude_check_in.required' => 'longitude tidak boleh kosong',
            'photo_check_in.required' => 'Foto tidak boleh kosong',
            'photo_check_in.image' => 'File harus berupa gambar',
            'photo_check_in.mimes' => 'File harus berupa gambar dengan format jpeg, png, jpg, atau gif',
        ];
    }
}
