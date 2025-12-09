<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDesaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only Admin and OperatorDesa can create new desa
        $user = $this->user();
        return $user && ($user->isAdmin() || $user->isOperatorDesa());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_desa' => 'required|string|max:255',
            'kecamatan' => 'required|string|max:255',
            'kabupaten' => 'required|string|max:255'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'nama_desa.required' => 'Nama desa wajib diisi',
            'nama_desa.max' => 'Nama desa maksimal 255 karakter',
            'kecamatan.required' => 'Nama kecamatan wajib diisi',
            'kecamatan.max' => 'Nama kecamatan maksimal 255 karakter',
            'kabupaten.required' => 'Nama kabupaten wajib diisi',
            'kabupaten.max' => 'Nama kabupaten maksimal 255 karakter',
        ];
    }
}
