<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetKalenderBulananRequest;
use App\Http\Requests\GetLapanganSlotsDailyRequest;
use App\Http\Resources\LapanganPublicResource;
use App\Models\Lapangan;
use App\Services\LapanganService;

class LapanganPublicController extends Controller
{
    protected LapanganService $lapangan_service;

    public function __construct(LapanganService $lapangan_service)
    {
        $this->lapangan_service = $lapangan_service;
    }

    public function getLapanganPublic()
    {
        $get = $this->lapangan_service->getLapanganPublic();

        return LapanganPublicResource::collection($get);
    }

    public function detailLapanganSlot(GetLapanganSlotsDailyRequest $request, Lapangan $lapangan)
    {
        if ($lapangan->status === 'tidak_aktif') {
            return response()->json([
                'message' => 'Lapangan sedang tidak aktif'
            ], 400);
        }

        $date = $request->date;

        $slot = $this->lapangan_service->detailLapanganSlot($date, $lapangan);

        return response()->json([
            'lapangan' => new LapanganPublicResource($lapangan),
            'slots' => $slot
        ], 200);
    }

    public function getKalenderBulanan(GetKalenderBulananRequest $request, Lapangan $lapangan)
    {
        if ($lapangan->status === 'tidak_aktif') {
            return response()->json([
                'message' => 'Lapangan sedang tidak aktif'
            ], 400);
        }

        $year = $request->year;
        $month = str_pad($request->month, 2, '0', STR_PAD_LEFT);

        $bulananFullBooking = $this->lapangan_service->getKalenderBulanan($year, $month, $lapangan);

        return response()->json([
            'court_id' => $lapangan->id,
            'year' => $year,
            'month' => $month,
            'fully_booked_dates' => $bulananFullBooking
        ], 200);
    }
}
