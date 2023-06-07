<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Amentities;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function allProperty() {
      $property = Property::get();
      return view('backend.property.all_property', compact('property'));
    }


    public function addProperty() {
      $propertyType = PropertyType::get();
      $amenities = Amentities::get();
      $activeAgent = User::where('status', 'active')->where('role', 'agent')->get();
      return view('backend.property.add_property', compact('propertyType', 'amenities', 'activeAgent'));
    }
}