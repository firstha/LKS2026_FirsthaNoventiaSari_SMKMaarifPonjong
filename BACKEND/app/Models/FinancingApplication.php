<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Payment;
use App\Models\Installment;
use App\Models\User;
use App\Models\BusinessVerification;

class FinancingApplication extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'business_verification_id',
        'jumlah_pembiayaan',
        'tenor_bulan',
        'tujuan_pembiayaan',
        'status',
        'submitted_at'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function businessVerification()
    {
        return $this->belongsTo(BusinessVerification::class);
    }
}
