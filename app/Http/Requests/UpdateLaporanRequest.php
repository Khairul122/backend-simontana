<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLaporanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth already enforced by jwt.auth middleware
    }

    public function rules(): array
    {
        return [
            'judul_laporan'       => 'sometimes|string|max:255',
            'deskripsi'           => 'sometimes|string',
            'tingkat_keparahan'   => 'sometimes|string|in:Rendah,Sedang,Tinggi,Kritis',
            'latitude'            => 'sometimes|numeric|between:-90,90',
            'longitude'           => 'sometimes|numeric|between:-180,180',
            'id_kategori_bencana' => 'sometimes|exists:kategori_bencana,id',
            'id_desa'             => 'sometimes|exists:desa,id',
            'alamat'              => 'nullable|string|max:500',
            'jumlah_korban'       => 'nullable|integer|min:0',
            'jumlah_rumah_rusak'  => 'nullable|integer|min:0',
            'is_prioritas'        => 'nullable|boolean',
            'data_tambahan'       => 'nullable|array',
            'foto_bukti_1'        => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
            'foto_bukti_2'        => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
            'foto_bukti_3'        => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
            'video_bukti'         => 'nullable|file|mimes:mp4,avi,mov|max:10240',
            'waktu_laporan'       => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'tingkat_keparahan.in' => 'Tingkat keparahan harus salah satu dari: Rendah, Sedang, Tinggi, Kritis.',
            'latitude.between'     => 'Latitude harus antara -90 dan 90.',
            'longitude.between'    => 'Longitude harus antara -180 dan 180.',
            'id_kategori_bencana.exists' => 'Kategori bencana tidak valid.',
            'id_desa.exists'       => 'Desa tidak ditemukan.',
            'foto_bukti_1.max'     => 'Foto bukti 1 maksimal 5MB.',
            'foto_bukti_2.max'     => 'Foto bukti 2 maksimal 5MB.',
            'foto_bukti_3.max'     => 'Foto bukti 3 maksimal 5MB.',
            'video_bukti.max'      => 'Video bukti maksimal 10MB.',
        ];
    }
}
