<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Tenant",
 *     title="Tenant",
 *     description="Tenant model",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="name", type="string", example="Acme Corp"),
 *     @OA\Property(property="domain", type="string", example="acme.example.com"),
 *     @OA\Property(property="database", type="string", example="tenant_acme"),
 *     @OA\Property(property="status", type="string", enum={"active", "inactive", "suspended"}, example="active"),
 *     @OA\Property(property="settings", type="object", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 * 
 * @OA\Schema(
 *     schema="TenantBasic",
 *     title="Tenant Basic",
 *     description="Basic tenant information",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="name", type="string", example="Acme Corp"),
 *     @OA\Property(property="domain", type="string", example="acme.example.com")
 * )
 */
class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'domain',
        'database',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'json',
    ];

    /**
     * Get all users associated with this tenant.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get all folders for this tenant.
     */
    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class);
    }

    /**
     * Get all files for this tenant.
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }
}
