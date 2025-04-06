<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentCategory;
use App\Models\PaymentMethod;
use App\Models\Payment;
use App\Models\PaymentType;

class PaymentFilterController extends Controller
{
    public function index()
    {
        $categories = PaymentCategory::select('id', 'name')->get();
        $methods = PaymentMethod::select('id', 'name')->get();
        $paymentTypes = PaymentType::select('id', 'name')->get();
        // get year from payment_date
        $years = Payment::selectRaw('EXTRACT(YEAR FROM payment_date) as year')->distinct()->orderBy('year', 'desc')->get();
        return response()->json([
            'data' => [
                'categories' => $categories, 
                'methods' => $methods, 
                'years' => $years,
                'payment_types' => $paymentTypes,
            ]
        ]);
    }
}
