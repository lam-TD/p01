<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class PaymentController extends ResourceController
{
    protected $defaultSort = ['-payment_date'];
    protected $allowableSorts = ['amount', 'payment_date'];
    protected $allowableIncludes = ['category.paymentType', 'method', 'user'];
    protected $allowableFilterScopes = ['date', 'months', 'year', 'type', 'category', 'method', 'description'];

    protected function model()
    {
        return Payment::class;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return parent::index($request);

        return response()->json([
            'message' => 'Hello, world!',
            'data' => $queryBuilder->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {;
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

}
