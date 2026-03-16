<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeeRequest;
use App\Models\Fee;

class FeeController extends Controller
{
    public function index()
    {
        return Fee::all();
    }

    public function store(FeeRequest $request)
    {
        return Fee::create($request->validated());
    }

    public function show(Fee $fee)
    {
        return $fee;
    }

    public function update(FeeRequest $request, Fee $fee)
    {
        $fee->update($request->validated());

        return $fee;
    }

    public function destroy(Fee $fee)
    {
        $fee->delete();

        return response()->json();
    }
}
