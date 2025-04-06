<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

abstract class ResourceController extends Controller
{
    protected $defaultSort = [];
    protected $allowableSorts = [];
    protected $allowableFields = [];
    protected $allowableFilters = [];
    protected $allowableIncludes = [];
    protected $allowableFilterScopes = [];

    abstract protected function model();


    public function index(Request $request)
    {
        $query = $this->newQueryBuilder($this->model(), $request);

        // response with pagination in meta
        $data = $query->paginate();

        return response()->json([
            'message' => 'Hello, world!',
            'data' => $data->items(),
            'meta' => [
                'pagination' => [
                    'total' => $data->total(),
                    'per_page' => $data->perPage(),
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                ],
            ],
        ]);
    }
    
    protected function newQueryBuilder($query, $request)
    {
        $query = QueryBuilder::for($query, $request);

        $query->defaultSort($this->defaultSort);
        $query->allowedSorts($this->allowableSorts);
        $query->allowedFields($this->allowableFields);
        $query->allowedIncludes($this->allowableIncludes);
       

        // Apply filter scopes
        $allowedFilterScopes = [];
        foreach ($this->allowableFilterScopes as $scope) {
            $allowedFilterScopes[] = AllowedFilter::scope($scope);
        }

        $query->allowedFilters([...$allowedFilterScopes, ...$this->allowableFilters]);

        return $query;
    }
    
    
    
}
