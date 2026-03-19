<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $targetUserId = (int) $this->route('id');
        $user = $this->user();

        if (!$user) {
            return false;
        }

        if ($user->role === 'Admin') {
            return true;
        }

        return (int) $user->id === $targetUserId;
    }

    public function rules(): array
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:pengguna,email,' . $this->route('id'),
            'no_telepon' => 'nullable|string|max:20|regex:/^[0-9\-\+\s]*$/',
            'alamat' => 'nullable|string|max:500',
            'id_desa' => 'nullable|exists:desa,id',
        ];

        if ($this->has('password')) {
            $rules['password'] = 'required|string|min:6';
        }

        return $rules;
    }

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
