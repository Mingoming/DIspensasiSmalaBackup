<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::all();

        return response()->json([
            'data' => $kelas,
        ]);
    }

    public function show($id)
    {
        $kelas = Kelas::with('siswa')->findOrFail($id);

        return response()->json([
            'data' => $kelas,
        ]);
    }
}
