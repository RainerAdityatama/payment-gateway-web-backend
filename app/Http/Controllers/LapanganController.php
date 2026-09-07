<?php

namespace App\Http\Controllers;

use App\Http\Requests\EditLapanganRequest;
use App\Http\Requests\TambahLapanganRequest;
use App\Http\Resources\LapanganAdminResource;
use App\Models\Lapangan;
use App\Services\LapanganService;

class LapanganController extends Controller
{
    protected LapanganService $lapanganService;

    public function __construct(LapanganService $lapanganService)
    {
        $this->lapanganService = $lapanganService;
    }

    public function getLapanganAdmin()
    {
        $get = $this->lapanganService->getLapanganAdmin();

        return LapanganAdminResource::collection($get);
    }

    public function tambahLapangan(TambahLapanganRequest $request)
    {
        $validated = $request->validated();

        $this->lapanganService->tambahLapangan($validated);

        return response()->json([
            'message' => 'Berhasil menambah data lapangan'
        ], 201);
    }

    public function editLapangan(EditLapanganRequest $request, Lapangan $lapangan)
    {
        $validated = $request->validated();

        $this->lapanganService->editLapangan($validated, $lapangan);

        return response()->json([
            'message' => 'Berhasil mengubah data lapangan'
        ], 200);
    }

    public function hapusLapangan(Lapangan $lapangan)
    {
        $this->lapanganService->hapusLapangan($lapangan);

        return response()->json([
            'message' => 'Berhasil menghapus data lapangan'
        ], 200);
    }
}
