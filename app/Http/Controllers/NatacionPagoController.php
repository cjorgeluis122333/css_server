<?php

namespace App\Http\Controllers;

use App\Http\Requests\NatacionPagoRequest;
use App\Models\activities\NatacionPago;

class NatacionPagoController extends Controller
{
    public function index()
    {
        return NatacionPago::all();
    }

    public function store(NatacionPagoRequest $request)
    {
        return NatacionPago::create($request->validated());
    }

    public function show(NatacionPago $natacionPago)
    {
        return $natacionPago;
    }

    public function update(NatacionPagoRequest $request, NatacionPago $natacionPago)
    {
        $natacionPago->update($request->validated());

        return $natacionPago;
    }

    public function destroy(NatacionPago $natacionPago)
    {
        $natacionPago->delete();

        return response()->json();
    }
}
