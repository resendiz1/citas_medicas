<?php

namespace App\Http\Controllers;

use App\Models\UserLog;
use Illuminate\Http\Request;

class UserLogController extends Controller
{
    public function storeGeo(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        UserLog::where('user_id', auth()->id())
            ->whereNull('lat')
            ->latest()
            ->limit(1)
            ->update([
                'lat' => $request->lat,
                'lng' => $request->lng,
            ]);

        return response()->json(['success' => true]);
    }
}
