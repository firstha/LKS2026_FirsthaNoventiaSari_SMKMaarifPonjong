<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusinessVerification;

class BusinessVerificationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nama_usaha' => 'required',
            'nib' => 'required',
            'npwp' => 'required',
            'omzet_bulanan' => 'required|numeric',
            'jumlah_karyawan' => 'required|integer',
            'lama_usaha_tahun' => 'required|integer'
        ]);

        $data = BusinessVerification::create([
            'user_id' => $request->user()->id,
            'nama_usaha' => $request->nama_usaha,
            'nib' => $request->nib,
            'npwp' => $request->npwp,
            'omzet_bulanan' => $request->omzet_bulanan,
            'jumlah_karyawan' => $request->jumlah_karyawan,
            'lama_usaha_tahun' => $request->lama_usaha_tahun,
            'status' => 'submitted'
        ]);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    public function approve(Request $request, $id)
    {
        $verification = BusinessVerification::findOrFail($id);

        if ($request->action == 'approve') {
            $verification->update([
                'status' => 'verified',
                'verified_by' => $request->user()->id,
                'verified_at' => now()
            ]);
        } else {
            $verification->update([
                'status' => 'rejected',
                'rejected_reason' => $request->reason
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Updated'
        ]);
    }
}
