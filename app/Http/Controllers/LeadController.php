<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;

class LeadController extends Controller
{
    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:leads,id',
            'status' => 'required|string'
        ]);

        $lead = Lead::find($validated['id']);
        $lead->status = $validated['status'];
        $lead->save();

        return response()->json(['success' => true]);
    }
}
