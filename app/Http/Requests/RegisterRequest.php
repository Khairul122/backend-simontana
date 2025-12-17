<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow all users to register
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:pengguna,username',
            'email' => 'required|string|email|max:255|unique:pengguna,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:Admin,PetugasBPBD,OperatorDesa,Warga',
            'no_telepon' => 'nullable|string|max:20|regex:/^[0-9\-\+\s]*$/',
            'alamat' => 'nullable|string|max:500',
            'id_desa' => 'nullable|exists:desa,id',
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
            'nama.required' => 'Nama wajib diisi',
            'nama.max' => 'Nama maksimal 255 karakter',
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah digunakan',
            'username.max' => 'Username maksimal 255 karakter',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'email.max' => 'Email maksimal 255 karakter',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'role.required' => 'Role wajib dipilih',
            'role.in' => 'Role tidak valid',
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
            'username' => 'Username',
            'email' => 'Email',
            'password' => 'Password',
            'password_confirmation' => 'Konfirmasi Password',
            'role' => 'Role',
            'no_telepon' => 'Nomor Telepon',
            'alamat' => 'Alamat',
            'id_desa' => 'Desa',
        ];
    }
}
