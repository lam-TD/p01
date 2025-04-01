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
 *     schema="File",
 *     title="File",
 *     description="File model",
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="name", type="string", example="document.pdf"),
 *     @OA\Property(property="original_name", type="string", example="Original Document.pdf"),
 *     @OA\Property(property="path", type="string", example="files/Documents/document.pdf"),
 *     @OA\Property(property="size", type="integer", example=12345),
 *     @OA\Property(property="human_size", type="string", example="12.34 KB"),
 *     @OA\Property(property="mime_type", type="string", example="application/pdf"),
 *     @OA\Property(property="extension", type="string", example="pdf"),
 *     @OA\Property(property="folder_id", type="integer", nullable=true, example=1),
 *     @OA\Property(
 *         property="folder",
 *         nullable=true,
 *         ref="#/components/schemas/Folder"
 *     ),
 *     @OA\Property(property="tenant_id", type="integer", example=1),
 *     @OA\Property(property="uploaded_by", type="integer", example=1),
 *     @OA\Property(
 *         property="uploader",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="John Doe")
 *     ),
 *     @OA\Property(property="status", type="boolean", example=true),
 *     @OA\Property(property="metadata", type="object", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
class File extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'original_name',
        'path',
        'size',
        'mime_type',
        'extension',
        'tenant_id',
        'folder_id',
        'uploaded_by',
        'status',
        'metadata',
    ];

    protected $casts = [
        'size' => 'integer',
        'status' => 'boolean',
        'metadata' => 'json',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function ($query) {
            $user = Auth::user();

            // Allow super-admin to see all tenants' files
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
     * Get the tenant that owns the file.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user who uploaded the file.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the folder that contains the file.
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get the versions of the file.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(FileVersion::class);
    }

    /**
     * Get the latest version of the file.
     */
    public function latestVersion()
    {
        return $this->versions()->latest()->first();
    }
}
