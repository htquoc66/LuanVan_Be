<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use App\Models\District;
use App\Models\Ward;



class AddressController extends Controller
{
    public function city(){
        $cities = City::all();
        return response()->json($cities);
    }

    public function district($city_id){
        $districts = City::find($city_id)->district;
        return response()->json($districts);
    }

    public function ward($district_id){
        $wards = District::find($district_id)->ward;
        return response()->json($wards);
    }

    
}
