<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FolderCollection;
use App\Http\Resources\FolderResource;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Folders",
 *     description="API endpoints for managing folders"
 * )
 */
class FolderController extends Controller
{
    /**
     * Display a listing of the folders.
     *
     * @OA\Get(
     *     path="/api/v1/folders",
     *     operationId="getFoldersList",
     *     tags={"Folders"},
     *     summary="Get list of folders",
     *     description="Returns list of folders with pagination",
     *     @OA\Parameter(
     *         name="parent_id",
     *         in="query",
     *         description="Filter by parent folder ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search folders by name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="order_by",
     *         in="query",
     *         description="Field to order by",
     *         required=false,
     *         @OA\Schema(type="string", default="name")
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Order direction",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="asc")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Folder")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Folder::query();

        // Filter by parent folder
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        // Order by
        $orderBy = $request->order_by ?? 'name';
        $direction = $request->direction ?? 'asc';
        $query->orderBy($orderBy, $direction);

        // Paginate
        $folders = $query->paginate($request->per_page ?? 15);

        return response()->json(new FolderCollection($folders));
    }

    /**
     * Store a newly created folder in storage.
     * 
     * @OA\Post(
     *     path="/api/v1/folders",
     *     operationId="storeFolder",
     *     tags={"Folders"},
     *     summary="Store new folder",
     *     description="Creates a new folder and returns it",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="New Folder", description="Folder name"),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, example=null, description="Parent folder ID"),
     *             @OA\Property(property="metadata", type="object", nullable=true, description="Additional metadata")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Folder created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Folder")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
        ]);

        // Ensure folder name is unique within parent
        if (Folder::where('parent_id', $request->parent_id)
            ->where('name', $request->name)
            ->exists()) {
            return response()->json([
                'message' => 'A folder with this name already exists in the parent folder.',
            ], 422);
        }

        // Generate folder path
        $path = $request->name;
        if ($request->parent_id) {
            $parentFolder = Folder::findOrFail($request->parent_id);
            $path = $parentFolder->path.'/'.$request->name;
        }

        // Create folder
        $folder = Folder::create([
            'name' => $request->name,
            'path' => $path,
            'tenant_id' => app('tenant')->id,
            'parent_id' => $request->parent_id,
            'created_by' => Auth::id(),
            'metadata' => $request->metadata ?? null,
        ]);

        return response()->json(new FolderResource($folder), 201);
    }

    /**
     * Display the specified folder.
     * 
     * @OA\Get(
     *     path="/api/v1/folders/{id}",
     *     operationId="getFolderById",
     *     tags={"Folders"},
     *     summary="Get folder information",
     *     description="Returns folder data",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Folder ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Folder")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Folder not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $folder = Folder::findOrFail($id);

        return response()->json(new FolderResource($folder));
    }

    /**
     * Update the specified folder in storage.
     * 
     * @OA\Put(
     *     path="/api/v1/folders/{id}",
     *     operationId="updateFolder",
     *     tags={"Folders"},
     *     summary="Update existing folder",
     *     description="Updates a folder and returns it",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Folder ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Folder", description="Folder name"),
     *             @OA\Property(property="parent_id", type="integer", nullable=true, example=null, description="Parent folder ID"),
     *             @OA\Property(property="metadata", type="object", nullable=true, description="Additional metadata")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Folder updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Folder")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Folder not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $folder = Folder::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'parent_id' => 'sometimes|nullable|exists:folders,id',
            'metadata' => 'sometimes|array',
        ]);

        // Prevent circular reference
        if ($request->has('parent_id') && $request->parent_id == $id) {
            return response()->json([
                'message' => 'A folder cannot be its own parent.',
            ], 422);
        }

        // Rename folder
        if ($request->has('name') && $request->name !== $folder->name) {
            // Check if folder with this name already exists in parent
            if (Folder::where('parent_id', $folder->parent_id)
                ->where('name', $request->name)
                ->where('id', '!=', $folder->id)
                ->exists()) {
                return response()->json([
                    'message' => 'A folder with this name already exists in the parent folder.',
                ], 422);
            }

            $oldName = $folder->name;
            $folder->name = $request->name;

            // Update path for this folder and all subfolders
            $oldPath = $folder->path;
            $newPath = $request->name;
            if ($folder->parent_id) {
                $parentFolder = Folder::findOrFail($folder->parent_id);
                $newPath = $parentFolder->path.'/'.$request->name;
            }

            $folder->path = $newPath;
            $folder->save();

            // Update all child folders paths
            $this->updateChildFolderPaths($folder->id, $oldPath, $newPath);
        }

        // Move folder to new parent
        if ($request->has('parent_id') && $request->parent_id !== $folder->parent_id) {
            // Check if folder with this name already exists in new parent
            if (Folder::where('parent_id', $request->parent_id)
                ->where('name', $folder->name)
                ->where('id', '!=', $folder->id)
                ->exists()) {
                return response()->json([
                    'message' => 'A folder with this name already exists in the new parent folder.',
                ], 422);
            }

            // Calculate new path
            $oldPath = $folder->path;
            $newPath = $folder->name;
            if ($request->parent_id) {
                $newParentFolder = Folder::findOrFail($request->parent_id);
                $newPath = $newParentFolder->path.'/'.$folder->name;
            }

            $folder->parent_id = $request->parent_id;
            $folder->path = $newPath;
            $folder->save();

            // Update all child folders paths
            $this->updateChildFolderPaths($folder->id, $oldPath, $newPath);
        }

        // Update metadata
        if ($request->has('metadata')) {
            $folder->metadata = $request->metadata;
            $folder->save();
        }

        return response()->json(new FolderResource($folder));
    }

    /**
     * Remove the specified folder from storage.
     * 
     * @OA\Delete(
     *     path="/api/v1/folders/{id}",
     *     operationId="deleteFolder",
     *     tags={"Folders"},
     *     summary="Delete folder",
     *     description="Deletes a folder if it's empty",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Folder ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Folder deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Folder not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Cannot delete non-empty folder"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $folder = Folder::findOrFail($id);

        // Check if folder contains files or subfolders
        if ($folder->files()->exists() || $folder->children()->exists()) {
            return response()->json([
                'message' => 'Cannot delete folder that contains files or subfolders.',
            ], 422);
        }

        // Delete folder record (using soft delete)
        $folder->delete();

        return response()->noContent();
    }

    /**
     * Recursively update paths for all child folders.
     *
     * @param  int  $parentId
     * @param  string  $oldParentPath
     * @param  string  $newParentPath
     * @return void
     */
    protected function updateChildFolderPaths($parentId, $oldParentPath, $newParentPath)
    {
        $childFolders = Folder::where('parent_id', $parentId)->get();

        foreach ($childFolders as $childFolder) {
            $childPath = $childFolder->path;
            $newChildPath = str_replace($oldParentPath, $newParentPath, $childPath);

            $childFolder->path = $newChildPath;
            $childFolder->save();

            // Recursively update children
            $this->updateChildFolderPaths($childFolder->id, $childPath, $newChildPath);
        }
    }
}
