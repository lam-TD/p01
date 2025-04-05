<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $year = $request->query('year');


        $queryBuilder = QueryBuilder::for(Payment::class);
        $queryBuilder->allowedIncludes(['category', 'method', 'user']);
        $queryBuilder->allowedFilters([
            AllowedFilter::scope('months'),
            AllowedFilter::scope('year'),
        ]);

        // $payments = Payment::with('category', 'method', 'user')
        // ->whereYear('payment_date', $year)
        // ->whereRaw('extract(month from payment_date) in (' . implode(',', $months) . ')')
        // ->get();

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
