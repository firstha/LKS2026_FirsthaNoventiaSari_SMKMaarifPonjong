<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinancingApplication;
use App\Models\Payment;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function createToken($financingId)
    {
        $financing = FinancingApplication::findOrFail($financingId);
        
        $installments = $financing->installments()->where('status', 'unpaid')->get();
        $totalAmount = $installments->sum('total_cicilan');
        
        $orderId = 'ORDER-' . $financing->id . '-' . Str::random(6);
        
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $totalAmount,
            ],
            'customer_details' => [
                'first_name' => $financing->user->name,
                'email' => $financing->user->email,
            ],
            'item_details' => [
                [
                    'id' => $financing->id,
                    'price' => (int) $totalAmount,
                    'quantity' => 1,
                    'name' => 'Pembayaran Cicilan - ' . $financing->businessVerification->nama_usaha,
                ]
            ]
        ];
        
        $snapToken = Snap::getSnapToken($params);
        
        $payment = Payment::create([
            'id' => (string) Str::uuid(),
            'financing_application_id' => $financing->id,
            'order_id' => $orderId,
            'snap_token' => $snapToken,
            'amount' => $totalAmount,
            'status' => 'pending'
        ]);
        
        return response()->json([
            'success' => true,
            'snap_token' => $snapToken,
            'payment' => $payment
        ]);
    }
    
    public function callback(Request $request)
    {
        try {
            $serverKey = config('midtrans.server_key');
            $hashed = hash("sha512", 
                $request->order_id . 
                $request->status_code . 
                $request->gross_amount . 
                $serverKey
            );
            
            if ($hashed !== $request->signature_key) {
                return response()->json(['message' => 'Invalid signature'], 403);
            }
            
            $payment = Payment::where('order_id', $request->order_id)->first();
            
            if (!$payment) {
                return response()->json(['message' => 'Payment not found'], 404);
            }
            
            $payment->update([
                'status' => $request->transaction_status,
                'payment_type' => $request->payment_type,
                'midtrans_payload' => json_encode($request->all())
            ]);
            
            if ($request->transaction_status == 'settlement') {
                $installments = $payment->financingApplication->installments()
                    ->where('status', 'unpaid')
                    ->get();
                
                foreach ($installments as $installment) {
                    $installment->update([
                        'status' => 'paid',
                        'paid_at' => now()
                    ]);
                }
            }
            
            return response()->json(['message' => 'OK'], 200);
            
        } catch (\Throwable $e) {
            \Log::error('Midtrans callback error: ' . $e->getMessage());
            return response()->json(['message' => 'Error'], 500);
        }
    }
    
    public function checkStatus($paymentId)
    {
        $payment = Payment::findOrFail($paymentId);
        
        $status = Transaction::status($payment->order_id);
        
        return response()->json([
            'success' => true,
            'order_id' => $payment->order_id,
            'status' => $status
        ]);
    }

    public function getPaymentUrl($paymentId)
    {
        $payment = Payment::findOrFail($paymentId);
        
        $snapUrl = 'https://app.sandbox.midtrans.com/snap/' . $payment->snap_token;
        
        return response()->json([
            'success' => true,
            'payment_url' => $snapUrl,
            'snap_token' => $payment->snap_token
        ]);
    }
    
}