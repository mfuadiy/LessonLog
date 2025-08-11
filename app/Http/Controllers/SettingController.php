<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function updateDate(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
        ]);

        Setting::where('key', 'tanggal_sistem')->update(['value' => $request->tanggal]);

        return response()->json(['success' => true, 'tanggal' => $request->tanggal]);
    }
}
