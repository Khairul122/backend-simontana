<?php

namespace App\Http\Controllers;

use App\Models\Provinsi;
use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Desa;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Wilayah",
 *     description="API endpoints untuk manajemen data Wilayah Administratif Indonesia"
 * )
 */
class WilayahController extends Controller
{
    /**
     * @OA\Get(
     *     path="/wilayah",
     *     tags={"Wilayah"},
     *     summary="Get all wilayah",
     *     description="Mengambil semua data wilayah dengan filter jenis dan pagination",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="jenis",
     *         in="query",
     *         description="Filter berdasarkan jenis wilayah (provinsi, kabupaten, kecamatan, desa)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"provinsi", "kabupaten", "kecamatan", "desa"})
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Include relasi (parent,children)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"parent", "children"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Jumlah item per halaman",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data wilayah berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nama", type="string", example="Aceh"),
     *                         @OA\Property(property="jenis", type="string", example="provinsi"),
     *                         @OA\Property(property="parent", type="object"),
     *                         @OA\Property(property="children", type="array", @OA\Items())
     *                     )
     *                 ),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=75)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $jenis = $request->get('jenis', null);
        $include = $request->get('include', null);
        $perPage = $request->get('per_page', 15);

        $data = collect([]);
        $total = 0;

        switch (strtolower($jenis)) {
            case 'provinsi':
                $query = Provinsi::query();
                if ($include) {
                    $includes = explode(',', $include);
                    foreach ($includes as $inc) {
                        if ($inc === 'children') {
                            $query->with('kabupatens');
                        }
                    }
                }
                $data = $query->orderBy('nama')->paginate($perPage);
                $total = $data->total();
                break;
            
            case 'kabupaten':
                $query = Kabupaten::query();
                if ($include) {
                    $includes = explode(',', $include);
                    foreach ($includes as $inc) {
                        if ($inc === 'parent') {
                            $query->with('provinsi');
                        } elseif ($inc === 'children') {
                            $query->with('kecamatans');
                        }
                    }
                }
                $data = $query->orderBy('nama')->paginate($perPage);
                $total = $data->total();
                break;
            
            case 'kecamatan':
                $query = Kecamatan::query();
                if ($include) {
                    $includes = explode(',', $include);
                    foreach ($includes as $inc) {
                        if ($inc === 'parent') {
                            $query->with('kabupaten');
                        } elseif ($inc === 'children') {
                            $query->with('desas');
                        }
                    }
                }
                $data = $query->orderBy('nama')->paginate($perPage);
                $total = $data->total();
                break;
            
            case 'desa':
                $query = Desa::query();
                if ($include) {
                    $includes = explode(',', $include);
                    foreach ($includes as $inc) {
                        if ($inc === 'parent') {
                            $query->with('kecamatan');
                        }
                    }
                }
                $data = $query->orderBy('nama')->paginate($perPage);
                $total = $data->total();
                break;
            
            default:
                // Jika tidak ada jenis, kembalikan semua jenis
                $provinsi = Provinsi::with('kabupatens')->orderBy('nama')->get();
                $kabupaten = Kabupaten::with('provinsi', 'kecamatans')->orderBy('nama')->get();
                $kecamatan = Kecamatan::with('kabupaten', 'desas')->orderBy('nama')->get();
                $desa = Desa::with('kecamatan')->orderBy('nama')->get();

                $data = collect([
                    'provinsi' => $provinsi,
                    'kabupaten' => $kabupaten,
                    'kecamatan' => $kecamatan,
                    'desa' => $desa
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Data wilayah berhasil diambil',
                    'data' => $data
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data wilayah berhasil diambil',
            'data' => $data
        ]);
    }

    /**
     * @OA\Get(
     *     path="/wilayah/{id}",
     *     tags={"Wilayah"},
     *     summary="Get specific wilayah by ID",
     *     description="Mengambil data wilayah berdasarkan ID - parameter jenis wajib untuk menentukan tabel",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID wilayah",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="jenis",
     *         in="query",
     *         description="Jenis wilayah (provinsi, kabupaten, kecamatan, desa)",
     *         required=true,
     *         @OA\Schema(type="string", enum={"provinsi", "kabupaten", "kecamatan", "desa"})
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Include relasi (parent, children)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"parent", "children"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Detail wilayah berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nama", type="string", example="Aceh"),
     *                 @OA\Property(property="jenis", type="string", example="provinsi"),
     *                 @OA\Property(property="parent", type="object"),
     *                 @OA\Property(property="children", type="array", @OA\Items())
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Wilayah not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Wilayah tidak ditemukan")
     *         )
     *     )
     * )
     */
    public function showById(Request $request, $id): JsonResponse
    {
        $jenis = $request->get('jenis');
        $include = $request->get('include', null);

        if (!$jenis) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter jenis wajib disertakan'
            ], 400);
        }

        $data = null;

        switch (strtolower($jenis)) {
            case 'provinsi':
                $query = Provinsi::query();
                if ($include) {
                    $includes = explode(',', $include);
                    foreach ($includes as $inc) {
                        if ($inc === 'children') {
                            $query->with('kabupatens');
                        }
                    }
                }
                $data = $query->find($id);
                break;

            case 'kabupaten':
                $query = Kabupaten::query();
                if ($include) {
                    $includes = explode(',', $include);
                    foreach ($includes as $inc) {
                        if ($inc === 'parent') {
                            $query->with('provinsi');
                        } elseif ($inc === 'children') {
                            $query->with('kecamatans');
                        }
                    }
                }
                $data = $query->find($id);
                break;

            case 'kecamatan':
                $query = Kecamatan::query();
                if ($include) {
                    $includes = explode(',', $include);
                    foreach ($includes as $inc) {
                        if ($inc === 'parent') {
                            $query->with('kabupaten');
                        } elseif ($inc === 'children') {
                            $query->with('desas');
                        }
                    }
                }
                $data = $query->find($id);
                break;

            case 'desa':
                $query = Desa::query();
                if ($include) {
                    $includes = explode(',', $include);
                    foreach ($includes as $inc) {
                        if ($inc === 'parent') {
                            $query->with('kecamatan');
                        }
                    }
                }
                $data = $query->find($id);
                break;

            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis wilayah tidak valid'
                ], 400);
        }

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Wilayah tidak ditemukan'
            ], 404);
        }

        // Tambahkan informasi jenis ke data
        $data->jenis = $jenis;

        return response()->json([
            'success' => true,
            'message' => 'Detail wilayah berhasil diambil',
            'data' => $data
        ]);
    }

    /**
     * @OA\Post(
     *     path="/wilayah",
     *     tags={"Wilayah"},
     *     summary="Create new wilayah",
     *     description="Membuat wilayah baru berdasarkan jenis - Hanya Admin yang dapat mengakses endpoint ini",
     *     security={{"jwt": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"jenis", "nama"},
     *             @OA\Property(property="jenis", type="string", example="kabupaten", description="Jenis wilayah", enum={"provinsi", "kabupaten", "kecamatan", "desa"}),
     *             @OA\Property(property="nama", type="string", example="Aceh Barat", description="Nama wilayah"),
     *             @OA\Property(property="id_parent", type="integer", example=1, description="ID parent sesuai jenis (wajib untuk kabupaten, kecamatan, desa)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Wilayah berhasil ditambahkan",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Wilayah berhasil ditambahkan"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nama", type="string", example="Aceh Barat"),
     *                 @OA\Property(property="jenis", type="string", example="kabupaten"),
     *                 @OA\Property(property="id_parent", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Data tidak valid",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validasi gagal"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Token tidak valid",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Hanya Admin yang dapat mengakses",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Akses ditolak: Hanya Admin yang dapat mengakses endpoint ini")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        // Cek role pengguna - hanya Admin yang bisa mengakses
        $user = $request->user();
        if (!$user || $user->role !== 'Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya Admin yang dapat mengakses endpoint ini'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'jenis' => 'required|in:provinsi,kabupaten,kecamatan,desa',
            'nama' => 'required|string|max:255',
            'id_parent' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) use ($request) {
                    $jenis = strtolower($request->jenis);
                    if (in_array($jenis, ['kabupaten', 'kecamatan', 'desa']) && !$value) {
                        $fail('ID parent wajib diisi untuk jenis ' . $jenis . '.');
                    }
                }
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $jenis = strtolower($request->jenis);
        $nama = $request->nama;
        $id_parent = $request->id_parent;

        switch ($jenis) {
            case 'provinsi':
                $data = [
                    'nama' => $nama
                ];
                $wilayah = Provinsi::create($data);
                break;
            
            case 'kabupaten':
                // Validasi apakah id_parent (id_provinsi) valid
                $provinsi = Provinsi::find($id_parent);
                if (!$provinsi) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Provinsi tidak ditemukan'
                    ], 404);
                }
                
                $data = [
                    'nama' => $nama,
                    'id_provinsi' => $id_parent
                ];
                $wilayah = Kabupaten::create($data);
                break;
            
            case 'kecamatan':
                // Validasi apakah id_parent (id_kabupaten) valid
                $kabupaten = Kabupaten::find($id_parent);
                if (!$kabupaten) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kabupaten tidak ditemukan'
                    ], 404);
                }
                
                $data = [
                    'nama' => $nama,
                    'id_kabupaten' => $id_parent
                ];
                $wilayah = Kecamatan::create($data);
                break;
            
            case 'desa':
                // Validasi apakah id_parent (id_kecamatan) valid
                $kecamatan = Kecamatan::find($id_parent);
                if (!$kecamatan) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kecamatan tidak ditemukan'
                    ], 404);
                }
                
                $data = [
                    'nama' => $nama,
                    'id_kecamatan' => $id_parent
                ];
                $wilayah = Desa::create($data);
                break;
            
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis wilayah tidak valid'
                ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Wilayah berhasil ditambahkan',
            'data' => [
                'id' => $wilayah->id,
                'nama' => $wilayah->nama,
                'jenis' => $jenis,
                'id_parent' => $jenis !== 'provinsi' ? $id_parent : null
            ]
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/wilayah/{id}",
     *     tags={"Wilayah"},
     *     summary="Update wilayah",
     *     description="Memperbarui data wilayah berdasarkan ID - parameter jenis wajib untuk menentukan tabel",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID wilayah",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"jenis", "nama"},
     *             @OA\Property(property="jenis", type="string", example="kabupaten", description="Jenis wilayah", enum={"provinsi", "kabupaten", "kecamatan", "desa"}),
     *             @OA\Property(property="nama", type="string", example="Aceh Barat Baru", description="Nama wilayah yang baru"),
     *             @OA\Property(property="id_parent", type="integer", example=1, description="ID parent sesuai jenis (wajib untuk kabupaten, kecamatan, desa)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Wilayah berhasil diperbarui",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Wilayah berhasil diperbarui"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nama", type="string", example="Aceh Barat Baru"),
     *                 @OA\Property(property="jenis", type="string", example="kabupaten"),
     *                 @OA\Property(property="id_parent", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Data tidak valid",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validasi gagal"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Token tidak valid",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Hanya Admin yang dapat mengakses",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Akses ditolak: Hanya Admin yang dapat mengakses endpoint ini")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Wilayah tidak ditemukan",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Wilayah tidak ditemukan")
     *         )
     *     )
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        // Cek role pengguna - hanya Admin yang bisa mengakses
        $user = $request->user();
        if (!$user || $user->role !== 'Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya Admin yang dapat mengakses endpoint ini'
            ], 403);
        }

        $jenis = $request->get('jenis');
        if (!$jenis) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter jenis wajib disertakan'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'jenis' => 'required|in:provinsi,kabupaten,kecamatan,desa',
            'nama' => 'required|string|max:255',
            'id_parent' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) use ($request) {
                    $jenis = strtolower($request->jenis);
                    if (in_array($jenis, ['kabupaten', 'kecamatan', 'desa']) && !$value) {
                        $fail('ID parent wajib diisi untuk jenis ' . $jenis . '.');
                    }
                }
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $nama = $request->nama;
        $id_parent = $request->id_parent;

        $model = null;
        $wilayah = null;

        switch (strtolower($jenis)) {
            case 'provinsi':
                $model = Provinsi::find($id);
                if (!$model) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Provinsi tidak ditemukan'
                    ], 404);
                }
                
                $model->update(['nama' => $nama]);
                $wilayah = $model;
                break;
            
            case 'kabupaten':
                $model = Kabupaten::find($id);
                if (!$model) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kabupaten tidak ditemukan'
                    ], 404);
                }
                
                // Validasi apakah id_parent (id_provinsi) valid
                if ($id_parent) {
                    $provinsi = Provinsi::find($id_parent);
                    if (!$provinsi) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Provinsi tidak ditemukan'
                        ], 404);
                    }
                }
                
                $model->update([
                    'nama' => $nama,
                    'id_provinsi' => $id_parent
                ]);
                $wilayah = $model;
                break;
            
            case 'kecamatan':
                $model = Kecamatan::find($id);
                if (!$model) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Kecamatan tidak ditemukan'
                    ], 404);
                }
                
                // Validasi apakah id_parent (id_kabupaten) valid
                if ($id_parent) {
                    $kabupaten = Kabupaten::find($id_parent);
                    if (!$kabupaten) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Kabupaten tidak ditemukan'
                        ], 404);
                    }
                }
                
                $model->update([
                    'nama' => $nama,
                    'id_kabupaten' => $id_parent
                ]);
                $wilayah = $model;
                break;
            
            case 'desa':
                $model = Desa::find($id);
                if (!$model) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Desa tidak ditemukan'
                    ], 404);
                }
                
                // Validasi apakah id_parent (id_kecamatan) valid
                if ($id_parent) {
                    $kecamatan = Kecamatan::find($id_parent);
                    if (!$kecamatan) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Kecamatan tidak ditemukan'
                        ], 404);
                    }
                }
                
                $model->update([
                    'nama' => $nama,
                    'id_kecamatan' => $id_parent
                ]);
                $wilayah = $model;
                break;
            
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis wilayah tidak valid'
                ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Wilayah berhasil diperbarui',
            'data' => [
                'id' => $wilayah->id,
                'nama' => $wilayah->nama,
                'jenis' => $jenis,
                'id_parent' => $jenis !== 'provinsi' ? $id_parent : null
            ]
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/wilayah/{id}",
     *     tags={"Wilayah"},
     *     summary="Delete wilayah",
     *     description="Menghapus data wilayah berdasarkan ID - parameter jenis wajib untuk menentukan tabel",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID wilayah",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\QueryParameter(
     *         name="jenis",
     *         in="query",
     *         description="Jenis wilayah (provinsi, kabupaten, kecamatan, desa)",
     *         required=true,
     *         @OA\Schema(type="string", enum={"provinsi", "kabupaten", "kecamatan", "desa"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Wilayah berhasil dihapus",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Wilayah berhasil dihapus")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Jenis tidak valid",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Jenis wilayah tidak valid")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Token tidak valid",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - Hanya Admin yang dapat mengakses",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Akses ditolak: Hanya Admin yang dapat mengakses endpoint ini")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Wilayah tidak ditemukan",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Wilayah tidak ditemukan")
     *         )
     *     )
     * )
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        // Cek role pengguna - hanya Admin yang bisa mengakses
        $user = $request->user();
        if (!$user || $user->role !== 'Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Hanya Admin yang dapat mengakses endpoint ini'
            ], 403);
        }

        $jenis = $request->get('jenis');
        if (!$jenis) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter jenis wajib disertakan'
            ], 400);
        }

        $model = null;
        $deleted = false;

        switch (strtolower($jenis)) {
            case 'provinsi':
                $model = Provinsi::find($id);
                if ($model) {
                    // Cek apakah ada kabupaten yang terkait
                    if ($model->kabupatens()->count() > 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Tidak dapat menghapus provinsi karena masih memiliki kabupaten terkait'
                        ], 400);
                    }
                    $deleted = $model->delete();
                }
                break;
            
            case 'kabupaten':
                $model = Kabupaten::find($id);
                if ($model) {
                    // Cek apakah ada kecamatan yang terkait
                    if ($model->kecamatans()->count() > 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Tidak dapat menghapus kabupaten karena masih memiliki kecamatan terkait'
                        ], 400);
                    }
                    $deleted = $model->delete();
                }
                break;
            
            case 'kecamatan':
                $model = Kecamatan::find($id);
                if ($model) {
                    // Cek apakah ada desa yang terkait
                    if ($model->desas()->count() > 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Tidak dapat menghapus kecamatan karena masih memiliki desa terkait'
                        ], 400);
                    }
                    $deleted = $model->delete();
                }
                break;
            
            case 'desa':
                $model = Desa::find($id);
                if ($model) {
                    // Cek apakah ada data terkait (pengguna, laporan)
                    if ($model->pengguna()->count() > 0 || $model->laporan()->count() > 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Tidak dapat menghapus desa karena masih memiliki data terkait'
                        ], 400);
                    }
                    $deleted = $model->delete();
                }
                break;
            
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis wilayah tidak valid'
                ], 400);
        }

        if (!$model) {
            return response()->json([
                'success' => false,
                'message' => 'Wilayah tidak ditemukan'
            ], 404);
        }

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Wilayah berhasil dihapus'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus wilayah'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/wilayah/provinsi",
     *     tags={"Wilayah"},
     *     summary="Get all provinsi",
     *     description="Mengambil semua data provinsi dengan opsi include relasi",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Include relasi (children untuk include kabupaten)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"children"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data provinsi berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama", type="string", example="Aceh"),
     *                     @OA\Property(property="kabupatens", type="array", @OA\Items())
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getAllProvinsi(Request $request): JsonResponse
    {
        $include = $request->get('include', null);
        $query = Provinsi::query();

        if ($include) {
            $includes = explode(',', $include);
            foreach ($includes as $inc) {
                if ($inc === 'children') {
                    $query->with('kabupatens');
                }
            }
        }

        $provinsi = $query->orderBy('nama')->get();

        return response()->json([
            'success' => true,
            'message' => 'Data provinsi berhasil diambil',
            'data' => $provinsi
        ]);
    }

    /**
     * @OA\Get(
     *     path="/wilayah/provinsi/{id}",
     *     tags={"Wilayah"},
     *     summary="Get provinsi by ID",
     *     description="Mengambil data provinsi berdasarkan ID dengan opsi include relasi",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID provinsi",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Include relasi (children untuk include kabupaten)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"children"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data provinsi berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nama", type="string", example="Aceh"),
     *                 @OA\Property(property="kabupatens", type="array", @OA\Items())
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Provinsi not found")
     * )
     */
    public function getProvinsiById(Request $request, $id): JsonResponse
    {
        $include = $request->get('include', null);
        $query = Provinsi::query();

        if ($include) {
            $includes = explode(',', $include);
            foreach ($includes as $inc) {
                if ($inc === 'children') {
                    $query->with('kabupatens');
                }
            }
        }

        $provinsi = $query->find($id);

        if (!$provinsi) {
            return response()->json([
                'success' => false,
                'message' => 'Provinsi tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data provinsi berhasil diambil',
            'data' => $provinsi
        ]);
    }

    /**
     * @OA\Get(
     *     path="/wilayah/kabupaten/{provinsi_id}",
     *     tags={"Wilayah"},
     *     summary="Get kabupaten by provinsi",
     *     description="Mengambil semua data kabupaten/kota berdasarkan ID provinsi",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="provinsi_id",
     *         in="path",
     *         description="ID provinsi",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Include relasi (parent untuk provinsi, children untuk kecamatan)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"parent", "children"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data kabupaten berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama", type="string", example="Aceh Barat"),
     *                     @OA\Property(property="id_provinsi", type="integer", example=1),
     *                     @OA\Property(property="provinsi", type="object"),
     *                     @OA\Property(property="kecamatans", type="array", @OA\Items())
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Provinsi not found")
     * )
     */
    public function getKabupatenByProvinsi(Request $request, $provinsi_id): JsonResponse
    {
        $provinsi = Provinsi::find($provinsi_id);

        if (!$provinsi) {
            return response()->json([
                'success' => false,
                'message' => 'Provinsi tidak ditemukan'
            ], 404);
        }

        $include = $request->get('include', null);
        $query = Kabupaten::where('id_provinsi', $provinsi_id);

        if ($include) {
            $includes = explode(',', $include);
            foreach ($includes as $inc) {
                if ($inc === 'parent') {
                    $query->with('provinsi');
                } elseif ($inc === 'children') {
                    $query->with('kecamatans');
                }
            }
        }

        $kabupaten = $query->orderBy('nama')->get();

        return response()->json([
            'success' => true,
            'message' => 'Data kabupaten berhasil diambil',
            'data' => $kabupaten
        ]);
    }

    /**
     * @OA\Get(
     *     path="/wilayah/kecamatan/{kabupaten_id}",
     *     tags={"Wilayah"},
     *     summary="Get kecamatan by kabupaten",
     *     description="Mengambil semua data kecamatan berdasarkan ID kabupaten",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="kabupaten_id",
     *         in="path",
     *         description="ID kabupaten",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Include relasi (parent untuk kabupaten, children untuk desa)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"parent", "children"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data kecamatan berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama", type="string", example="Bakongan"),
     *                     @OA\Property(property="id_kabupaten", type="integer", example=1),
     *                     @OA\Property(property="kabupaten", type="object"),
     *                     @OA\Property(property="desas", type="array", @OA\Items())
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Kabupaten not found")
     * )
     */
    public function getKecamatanByKabupaten(Request $request, $kabupaten_id): JsonResponse
    {
        $kabupaten = Kabupaten::find($kabupaten_id);

        if (!$kabupaten) {
            return response()->json([
                'success' => false,
                'message' => 'Kabupaten tidak ditemukan'
            ], 404);
        }

        $include = $request->get('include', null);
        $query = Kecamatan::where('id_kabupaten', $kabupaten_id);

        if ($include) {
            $includes = explode(',', $include);
            foreach ($includes as $inc) {
                if ($inc === 'parent') {
                    $query->with('kabupaten');
                } elseif ($inc === 'children') {
                    $query->with('desas');
                }
            }
        }

        $kecamatan = $query->orderBy('nama')->get();

        return response()->json([
            'success' => true,
            'message' => 'Data kecamatan berhasil diambil',
            'data' => $kecamatan
        ]);
    }

    /**
     * Get desa by kecamatan ID
     */
    /**
     * @OA\Get(
     *     path="/wilayah/desa/{kecamatan_id}",
     *     tags={"Wilayah"},
     *     summary="Get desa by kecamatan",
     *     description="Mengambil semua data desa/kelurahan berdasarkan ID kecamatan",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="kecamatan_id",
     *         in="path",
     *         description="ID kecamatan",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Include relasi (parent untuk kecamatan)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"parent"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data desa berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama", type="string", example="Ie Meule"),
     *                     @OA\Property(property="id_kecamatan", type="integer", example=1),
     *                     @OA\Property(property="kecamatan", type="object")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Kecamatan not found")
     * )
     */
    public function getDesaByKecamatan(Request $request, $kecamatan_id): JsonResponse
    {
        $kecamatan = Kecamatan::find($kecamatan_id);

        if (!$kecamatan) {
            return response()->json([
                'success' => false,
                'message' => 'Kecamatan tidak ditemukan'
            ], 404);
        }

        $include = $request->get('include', null);
        $query = Desa::where('id_kecamatan', $kecamatan_id);

        if ($include) {
            $includes = explode(',', $include);
            foreach ($includes as $inc) {
                if ($inc === 'parent') {
                    $query->with('kecamatan');
                }
            }
        }

        $desa = $query->orderBy('nama')->get();

        return response()->json([
            'success' => true,
            'message' => 'Data desa berhasil diambil',
            'data' => $desa
        ]);
    }

    /**
     * @OA\Get(
     *     path="/wilayah/detail/{desa_id}",
     *     tags={"Wilayah"},
     *     summary="Get wilayah detail by desa ID",
     *     description="Mengambil data wilayah lengkap berdasarkan ID desa (desa -> kecamatan -> kabupaten -> provinsi)",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="desa_id",
     *         in="path",
     *         description="ID desa",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Detail wilayah berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nama", type="string", example="Ie Meule"),
     *                 @OA\Property(property="id_kecamatan", type="integer", example=1),
     *                 @OA\Property(
     *                     property="kecamatan",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nama", type="string", example="Bakongan"),
     *                     @OA\Property(property="id_kabupaten", type="integer", example=1),
     *                     @OA\Property(
     *                         property="kabupaten",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nama", type="string", example="Aceh Selatan"),
     *                         @OA\Property(property="id_provinsi", type="integer", example=1),
     *                         @OA\Property(
     *                             property="provinsi",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="nama", type="string", example="Aceh")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Desa not found")
     * )
     */
    public function getWilayahDetailByDesaId($desa_id): JsonResponse
    {
        $desa = Desa::with(['kecamatan.kabupaten.provinsi'])->find($desa_id);

        if (!$desa) {
            return response()->json([
                'success' => false,
                'message' => 'Desa tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail wilayah berhasil diambil',
            'data' => $desa
        ]);
    }

    /**
     * @OA\Get(
     *     path="/wilayah/hierarchy/{desa_id}",
     *     tags={"Wilayah"},
     *     summary="Get wilayah hierarchy by desa ID",
     *     description="Mengambil hirarki wilayah lengkap berdasarkan ID desa (desa, kecamatan, kabupaten, provinsi dalam format terstruktur)",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="desa_id",
     *         in="path",
     *         description="ID desa",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Hirarki wilayah berhasil diambil"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="desa", type="object"),
     *                 @OA\Property(property="kecamatan", type="object"),
     *                 @OA\Property(property="kabupaten", type="object"),
     *                 @OA\Property(property="provinsi", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Desa not found")
     * )
     */
    public function getWilayahHierarchyByDesaId($desa_id): JsonResponse
    {
        $desa = Desa::with(['kecamatan.kabupaten.provinsi'])->find($desa_id);

        if (!$desa) {
            return response()->json([
                'success' => false,
                'message' => 'Desa tidak ditemukan'
            ], 404);
        }

        $hierarchy = [
            'desa' => $desa,
            'kecamatan' => $desa->kecamatan,
            'kabupaten' => $desa->kecamatan->kabupaten,
            'provinsi' => $desa->kecamatan->kabupaten->provinsi
        ];

        return response()->json([
            'success' => true,
            'message' => 'Hirarki wilayah berhasil diambil',
            'data' => $hierarchy
        ]);
    }

    /**
     * @OA\Get(
     *     path="/wilayah/search",
     *     tags={"Wilayah"},
     *     summary="Search wilayah",
     *     description="Mencari data wilayah berdasarkan nama",
     *     security={{"jwt": {}}},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Kata kunci pencarian",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="jenis",
     *         in="query",
     *         description="Filter berdasarkan jenis wilayah (provinsi, kabupaten, kecamatan, desa)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"provinsi", "kabupaten", "kecamatan", "desa"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Hasil pencarian wilayah"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="provinsi", type="array", @OA\Items()),
     *                 @OA\Property(property="kabupaten", type="array", @OA\Items()),
     *                 @OA\Property(property="kecamatan", type="array", @OA\Items()),
     *                 @OA\Property(property="desa", type="array", @OA\Items())
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Parameter pencarian (q) wajib disertakan")
     * )
     */
    public function search(Request $request): JsonResponse
    {
        $q = $request->get('q', '');
        $jenis = $request->get('jenis', null);

        if (empty($q)) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter pencarian (q) wajib disertakan'
            ], 400);
        }

        $results = collect([]);

        switch (strtolower($jenis)) {
            case 'provinsi':
                $results = Provinsi::where('nama', 'LIKE', "%{$q}%")->get();
                break;

            case 'kabupaten':
                $results = Kabupaten::where('nama', 'LIKE', "%{$q}%")->with('provinsi')->get();
                break;

            case 'kecamatan':
                $results = Kecamatan::where('nama', 'LIKE', "%{$q}%")->with('kabupaten')->get();
                break;

            case 'desa':
                $results = Desa::where('nama', 'LIKE', "%{$q}%")->with('kecamatan')->get();
                break;

            default:
                // Cari di semua jenis
                $provinsi = Provinsi::where('nama', 'LIKE', "%{$q}%")->get();
                $kabupaten = Kabupaten::where('nama', 'LIKE', "%{$q}%")->with('provinsi')->get();
                $kecamatan = Kecamatan::where('nama', 'LIKE', "%{$q}%")->with('kabupaten')->get();
                $desa = Desa::where('nama', 'LIKE', "%{$q}%")->with('kecamatan')->get();

                $results = collect([
                    'provinsi' => $provinsi,
                    'kabupaten' => $kabupaten,
                    'kecamatan' => $kecamatan,
                    'desa' => $desa
                ]);
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Hasil pencarian wilayah',
            'data' => $results
        ]);
    }
}