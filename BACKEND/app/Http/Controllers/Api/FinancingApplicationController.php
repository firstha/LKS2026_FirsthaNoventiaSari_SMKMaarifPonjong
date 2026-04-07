<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinancingApplication;
use App\Models\BusinessVerification;

class FinancingApplicationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'jumlah_pembiayaan' => 'required|numeric',
            'tenor_bulan' => 'required|in:6,12,24',
            'tujuan_pembiayaan' => 'required'
        ]);

        $user = $request->user();

        $business = BusinessVerification::where('user_id', $user->id)->first();

        // belum verif
        if (!$business || $business->status !== 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'Usaha belum terverifikasi'
            ], 400);
        }

        // minim usaha 1th
        if ($business->lama_usaha_tahun < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Minimal usaha 1 tahun'
            ], 400);
        }

        // max 3x omset
        if ($request->jumlah_pembiayaan > ($business->omzet_bulanan * 3)) {
            return response()->json([
                'success' => false,
                'message' => 'Melebihi batas pembiayaan'
            ], 400);
        }

        // sdh ada pengajuan aktif
        $active = FinancingApplication::where('user_id', $user->id)
            ->whereIn('status', ['submitted','under_review','recommended'])
            ->exists();

        if ($active) {
            return response()->json([
                'success' => false,
                'message' => 'Masih ada pengajuan aktif'
            ], 400);
        }

        // minyimpan
        $data = FinancingApplication::create([
            'user_id' => $user->id,
            'business_verification_id' => $business->id,
            'jumlah_pembiayaan' => $request->jumlah_pembiayaan,
            'tenor_bulan' => $request->tenor_bulan,
            'tujuan_pembiayaan' => $request->tujuan_pembiayaan,
            'status' => 'submitted',
            'submitted_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
