<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $currentUserRole = session('user_role');
        $currentUserId = session('user_id');
        $targetUserId = $this->route('id');

        // Admin can update any user
        if ($currentUserRole === 'Admin') {
            return true;
        }

        // User can update their own profile
        if ($currentUserId == $targetUserId) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:pengguna,email,' . $this->route('id'),
            'no_telepon' => 'nullable|string|max:20|regex:/^[0-9\-\+\s]*$/',
            'alamat' => 'nullable|string|max:500',
            'id_desa' => 'nullable|exists:desa,id',
        ];

        // Add password validation if present
        if ($this->has('password')) {
            $rules['password'] = 'required|string|min:6';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama wajib diisi',
            'nama.max' => 'Nama maksimal 255 karakter',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'email.max' => 'Email maksimal 255 karakter',
            'no_telepon.regex' => 'Format nomor telepon tidak valid',
            'alamat.max' => 'Alamat maksimal 500 karakter',
            'id_desa.exists' => 'Desa tidak ditemukan',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama' => 'Nama Lengkap',
            'email' => 'Email',
            'password' => 'Password',
            'no_telepon' => 'Nomor Telepon',
            'alamat' => 'Alamat',
            'id_desa' => 'Desa',
        ];
    }
}
