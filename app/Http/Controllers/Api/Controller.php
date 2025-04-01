<?php

namespace App\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Multi-Tenant File Management API Documentation",
 *      description="API documentation for a multi-tenant file management system",
 *      @OA\Contact(
 *          email="admin@example.com"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *      securityScheme="bearerAuth",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="JWT"
 * )
 *
 * @OA\Tag(
 *     name="Files",
 *     description="API Endpoints for File Management"
 * )
 * @OA\Tag(
 *     name="Folders",
 *     description="API Endpoints for Folder Management"
 * )
 * @OA\Tag(
 *     name="Users",
 *     description="API Endpoints for User Management"
 * )
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints for Authentication"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
} 