<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\FinancingApplication;

class Payment extends Model
{
    use HasFactory;
    
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'id', 'financing_application_id', 'order_id', 
        'snap_token', 'payment_type', 'status', 'amount', 'midtrans_payload'
    ];
    
    public function financingApplication()
    {
        return $this->belongsTo(FinancingApplication::class);
    }
}