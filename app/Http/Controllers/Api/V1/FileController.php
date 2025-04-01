<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\FileCollection;
use App\Http\Resources\FileResource;
use App\Models\File;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    /**
     * Display a listing of the files.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @OA\Get(
     *     path="/api/v1/files",
     *     summary="Get a list of files",
     *     description="Returns a paginated list of files with optional filtering",
     *     operationId="getFilesList",
     *     tags={"Files"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="folder_id",
     *         in="query",
     *         description="Filter files by folder ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search files by name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="extension",
     *         in="query",
     *         description="Filter files by extension",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="order_by",
     *         in="query",
     *         description="Field to order by (default: created_at)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"name", "created_at", "size"})
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Order direction (default: desc)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page (default: 15)",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/File")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = File::query();

        // Filter by folder
        if ($request->has('folder_id')) {
            $query->where('folder_id', $request->folder_id);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Filter by extension
        if ($request->has('extension')) {
            $query->where('extension', $request->extension);
        }

        // Order by
        $orderBy = $request->order_by ?? 'created_at';
        $direction = $request->direction ?? 'desc';
        $query->orderBy($orderBy, $direction);

        // Paginate
        $files = $query->paginate($request->per_page ?? 15);

        return response()->json(new FileCollection($files));
    }

    /**
     * Store a newly created file in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @OA\Post(
     *     path="/api/v1/files",
     *     summary="Upload a new file",
     *     description="Uploads a new file to the storage",
     *     operationId="storeFile",
     *     tags={"Files"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="The file to upload"
     *                 ),
     *                 @OA\Property(
     *                     property="folder_id",
     *                     type="integer",
     *                     description="Folder ID to store the file in"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="File uploaded successfully",
     *         @OA\JsonContent(ref="#/components/schemas/File")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:'.config('filesystems.max_upload_size', 10240),
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $extension = $uploadedFile->getClientOriginalExtension();
        $mimeType = $uploadedFile->getMimeType();
        $size = $uploadedFile->getSize();

        // Generate a safe filename
        $name = pathinfo($originalName, PATHINFO_FILENAME);
        $safeName = Str::slug($name).'.'.$extension;

        // Ensure filename is unique within the folder
        $folder = null;
        if ($request->folder_id) {
            $folder = Folder::findOrFail($request->folder_id);
        }

        // Check if file with this name already exists in folder
        $count = 1;
        $nameToCheck = $safeName;
        while (File::where('folder_id', $request->folder_id)
            ->where('name', $nameToCheck)
            ->exists()) {
            $nameToCheck = Str::slug($name).'-'.$count.'.'.$extension;
            $count++;
        }

        // Store the file
        $path = $uploadedFile->storeAs(
            'files/'.($folder ? $folder->path : ''),
            $nameToCheck,
            'tenant'
        );

        // Create file record
        $file = File::create([
            'name' => $nameToCheck,
            'original_name' => $originalName,
            'path' => $path,
            'size' => $size,
            'mime_type' => $mimeType,
            'extension' => $extension,
            'tenant_id' => app('tenant')->id,
            'folder_id' => $request->folder_id,
            'uploaded_by' => Auth::id(),
            'status' => true,
        ]);

        return response()->json(new FileResource($file), 201);
    }

    /**
     * Display the specified file.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     * 
     * @OA\Get(
     *     path="/api/v1/files/{id}",
     *     summary="Get file details",
     *     description="Returns detailed information about a file",
     *     operationId="getFileInfo",
     *     tags={"Files"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="File ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/File")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File not found"
     *     )
     * )
     */
    public function show($id)
    {
        $file = File::findOrFail($id);

        return response()->json(new FileResource($file));
    }

    /**
     * Update the specified file in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     * 
     * @OA\Put(
     *     path="/api/v1/files/{id}",
     *     summary="Update file details",
     *     description="Updates a file's information",
     *     operationId="updateFile",
     *     tags={"Files"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="File ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="folder_id", type="integer"),
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="metadata", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/File")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $file = File::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'folder_id' => 'sometimes|nullable|exists:folders,id',
            'status' => 'sometimes|boolean',
            'metadata' => 'sometimes|array',
        ]);

        // Rename file
        if ($request->has('name') && $request->name !== $file->name) {
            $extension = pathinfo($file->name, PATHINFO_EXTENSION);
            $newName = $request->name.'.'.$extension;

            // Check if file with this name already exists in folder
            if (File::where('folder_id', $file->folder_id)
                ->where('name', $newName)
                ->where('id', '!=', $file->id)
                ->exists()) {
                return response()->json([
                    'message' => 'A file with this name already exists in the folder.',
                ], 422);
            }

            $file->name = $newName;
        }

        // Move file to different folder
        if ($request->has('folder_id') && $request->folder_id !== $file->folder_id) {
            $newFolder = $request->folder_id ? Folder::findOrFail($request->folder_id) : null;
            $newPath = 'files/'.($newFolder ? $newFolder->path : '').'/'.$file->name;

            // Check if file with this name already exists in target folder
            if (File::where('folder_id', $request->folder_id)
                ->where('name', $file->name)
                ->where('id', '!=', $file->id)
                ->exists()) {
                return response()->json([
                    'message' => 'A file with this name already exists in the target folder.',
                ], 422);
            }

            // Move file in storage
            Storage::disk('tenant')->move($file->path, $newPath);

            $file->folder_id = $request->folder_id;
            $file->path = $newPath;
        }

        // Update status
        if ($request->has('status')) {
            $file->status = $request->status;
        }

        // Update metadata
        if ($request->has('metadata')) {
            $file->metadata = $request->metadata;
        }

        $file->save();

        return response()->json(new FileResource($file));
    }

    /**
     * Remove the specified file from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     * 
     * @OA\Delete(
     *     path="/api/v1/files/{id}",
     *     summary="Delete a file",
     *     description="Deletes a file (soft delete)",
     *     operationId="deleteFile",
     *     tags={"Files"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="File ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="File deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        $file = File::findOrFail($id);

        // Delete file from storage
        Storage::disk('tenant')->delete($file->path);

        // Delete file record (using soft delete)
        $file->delete();

        return response()->noContent();
    }

    /**
     * Download the specified file.
     *
     * @param  int  $id
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     * 
     * @OA\Get(
     *     path="/api/v1/files/{id}/download",
     *     summary="Download a file",
     *     description="Downloads the file with the original filename",
     *     operationId="downloadFile",
     *     tags={"Files"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="File ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File downloaded successfully",
     *         @OA\Header(
     *             header="Content-Type",
     *             description="File MIME type",
     *             @OA\Schema(type="string")
     *         ),
     *         @OA\Header(
     *             header="Content-Disposition",
     *             description="Attachment with original filename",
     *             @OA\Schema(type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="File not found"
     *     )
     * )
     */
    public function download($id)
    {
        $file = File::findOrFail($id);

        return Storage::disk('tenant')->download($file->path, $file->original_name);
    }
}
