<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class FileVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'path',
        'size',
        'version',
        'uploaded_by',
        'metadata',
    ];

    protected $casts = [
        'size' => 'integer',
        'version' => 'integer',
        'metadata' => 'json',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function ($query) {
            $user = Auth::user();

            // Allow super-admin to see all tenants' file versions
            if ($user && $user->hasRole('super-admin')) {
                return;
            }

            // Otherwise, filter by the current tenant via the file relationship
            if (app()->has('tenant')) {
                $query->whereHas('file', function ($query) {
                    $query->where('tenant_id', app('tenant')->id);
                });
            }
        });
    }

    /**
     * Get the file that the version belongs to.
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the user who uploaded the version.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
