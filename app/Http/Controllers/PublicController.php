<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicController extends Controller
{
    public function get_location() {
        $locations = DB::table('locations')->get();

        return response()->json(['locations' => $locations], 200);
    }
}
