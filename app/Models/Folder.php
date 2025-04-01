<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Schema(
 *     schema="Folder",
 *     title="Folder",
 *     description="Folder model",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="name", type="string", example="Documents"),
 *     @OA\Property(property="path", type="string", example="/Documents"),
 *     @OA\Property(property="tenant_id", type="integer", example=1),
 *     @OA\Property(property="parent_id", type="integer", nullable=true, example=null),
 *     @OA\Property(
 *         property="parent",
 *         nullable=true,
 *         description="Parent folder",
 *         ref="#/components/schemas/Folder"
 *     ),
 *     @OA\Property(property="created_by", type="integer", example=1),
 *     @OA\Property(
 *         property="creator",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="John Doe")
 *     ),
 *     @OA\Property(property="metadata", type="object", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
class Folder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'path',
        'tenant_id',
        'parent_id',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function ($query) {
            $user = Auth::user();

            // Allow super-admin to see all tenants' folders
            if ($user && $user->hasRole('super-admin')) {
                return;
            }

            // Otherwise, filter by the current tenant
            if (app()->has('tenant')) {
                $query->where('tenant_id', app('tenant')->id);
            }
        });
    }

    /**
     * Get the tenant that owns the folder.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user who created the folder.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the parent folder.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    /**
     * Get the subfolders in this folder.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    /**
     * Get the files in this folder.
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    /**
     * Get all descendants of the folder.
     */
    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }
}
