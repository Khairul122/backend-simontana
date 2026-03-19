<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        return true; 
    }

    
    public function rules(): array
    {
        return [
            'username' => 'required|string',
            'password' => 'required|string',
        ];
    }

    protected function prepareForValidation(): void
    {
        $identifier = $this->input('username')
            ?? $this->input('email')
            ?? $this->input('login');

        if ($identifier !== null) {
            $this->merge([
                'username' => $identifier,
            ]);
        }
    }

    
    public function messages(): array
    {
        return [
            'username.required' => 'Username wajib diisi',
            'password.required' => 'Password wajib diisi',
        ];
    }

    
    public function attributes(): array
    {
        return [
            'username' => 'Username',
            'password' => 'Password',
        ];
    }
}
