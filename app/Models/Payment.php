<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\PaymentFactory;
class Payment extends Model
{
    use HasFactory;
    public function category()
    {
        return $this->belongsTo(PaymentCategory::class, 'payment_category_id');
    }

    public function method()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeMonths($query, $months)
    {
        $months = str_replace('-', ',', $months);
        return $query->whereRaw('extract(month from payment_date) in (' . $months . ')');
    }

    public function scopeYear($query, $year)
    {
        return $query->whereRaw('extract(year from payment_date) = ' . $year);
    }


    protected static function newFactory()
    {
        return PaymentFactory::new();
    }
}
