<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\FinancingApplication;

class Installment extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id','financing_application_id','installment_number',
        'jatuh_tempo','nominal_pokok','nominal_bunga','total_cicilan','status'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
    
    public function financingApplication()
    {
        return $this->belongsTo(FinancingApplication::class);
    }
}
