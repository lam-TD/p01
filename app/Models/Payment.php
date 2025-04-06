<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'amount',
        'payment_category_id',
        'payment_method_id',
        'description',
        'user_id',
        'payment_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    protected static function newFactory()
    {
        return PaymentFactory::new();
    }

    /**
     * Get the category that owns the payment.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PaymentCategory::class, 'payment_category_id');
    }

    /**
     * Get the method that owns the payment.
     */
    public function method(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    /**
     * Get the user that owns the payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeDate($query, $date)
    {
        return $query->whereDate('payment_date', $date);
    }

    public function scopeMonths($query, ...$months)
    {
        $months = implode(',', $months);
        return $query->whereRaw('extract(month from payment_date) in (' . $months . ')');
    }

    public function scopeYear($query, $year)
    {
        return $query->whereRaw('extract(year from payment_date) = ' . $year);
    }

    public function scopeType($query, ...$type)
    {
        return $query->whereHas('category', function ($query) use ($type) {
            $query->whereIn('payment_type_id', $type);
        });
    }

    public function scopeCategory($query, ...$category)
    {
        return $query->whereIn('payment_category_id', $category);
    }

    public function scopeMethod($query, ...$method) 
    {
        return $query->whereIn('payment_method_id', $method);
    }

    public function scopeDescription($query, $description)
    {
        return $query->where('description', 'ilike', '%' . $description . '%');
    }
}
