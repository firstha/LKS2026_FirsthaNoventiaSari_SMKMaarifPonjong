<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinancingApplication;
use App\Models\BusinessVerification;
use App\Models\Installment;

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

    public function analyze(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:review,recommend,reject',
            'skor_kelayakan' => 'nullable|integer|min:1|max:100',
            'rekomendasi_limit' => 'nullable|numeric',
            'catatan_analisis' => 'nullable|string'
        ]);

        $app = FinancingApplication::findOrFail($id);

        //validasi ststua
        if (!in_array($app->status, ['submitted','under_review'])) {
            return response()->json([
                'success' => false,
                'message' => 'Status tidak valid'
            ], 400);
        }

        if ($request->action === 'review') {
            $app->update([
                'status' => 'under_review'
            ]);
        }

        elseif ($request->action === 'recommend') {
            $app->update([
                'status' => 'recommended',
                'skor_kelayakan' => $request->skor_kelayakan,
                'rekomendasi_limit' => $request->rekomendasi_limit,
                'catatan_analisis' => $request->catatan_analisis
            ]);
        }

        elseif ($request->action === 'reject') {
            $app->update([
                'status' => 'rejected_by_analyst',
                'rejected_reason' => $request->catatan_analisis
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $app
        ]);
    }

    public function approve(Request $request, $id)
    {
        $app = FinancingApplication::findOrFail($id);

        if ($app->status !== 'recommended') {
            return response()->json([
                'success' => false,
                'message' => 'Belum direkomendasikan'
            ], 400);
        }

        if ($request->action === 'approve') {

            $pokok = $app->jumlah_pembiayaan;
            $tenor = $app->tenor_bulan;

            $bunga = $pokok * 0.06 * ($tenor / 12);
            $total = $pokok + $bunga;
            $cicilan = $total / $tenor;

            $app->update([
                'status' => 'approved',
                'approved_at' => now()
            ]);

            //gnerete cicilan
            for ($i = 1; $i <= $tenor; $i++) {
                Installment::create([
                    'financing_application_id' => $app->id,
                    'installment_number' => $i,
                    'jatuh_tempo' => now()->addMonths($i),
                    'nominal_pokok' => $pokok / $tenor,
                    'nominal_bunga' => $bunga / $tenor,
                    'total_cicilan' => $cicilan,
                    'status' => 'unpaid'
                ]);
            }

        } else {

            $app->update([
                'status' => 'rejected_by_manager',
                'rejected_reason' => $request->reason
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Processed'
        ]);
    }
}
