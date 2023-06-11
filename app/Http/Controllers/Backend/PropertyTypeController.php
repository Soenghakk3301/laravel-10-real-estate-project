<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Amentities;
use App\Models\PropertyType;
use Illuminate\Http\Request;

class PropertyTypeController extends Controller
{
    public function allType()
    {
        $types = PropertyType::get();
        return view('backend.type.all_type', compact('types'));
    }


    public function addType()
    {
        return view('backend.type.add_type');
    }

    public function storeType(Request $request)
    {
        // validation
        $request->validate([
           'type_name' => 'required|unique:property_types|max:200',
           'type_icon' => 'required',
        ]);

        PropertyType::insert([
           'type_name' => $request->type_name,
           'type_icon' => $request->type_icon,
        ]);

        $notification = array(
           'message' => 'Property Type Create Successfully.',
           'alert-type' => 'success',
        );

        return redirect()->route('all.type')->with($notification);
    }

    public function editType($id)
    {
        $type = PropertyType::findOrFail($id);
        return view('backend.type.edit_type', compact('type'));
    }

    public function updateType(Request $request)
    {
        $id = $request->id;

        PropertyType::findOrFail($id)->update([
           'type_name' => $request->type_name,
           'type_icon' => $request->type_icon,
        ]);

        $notification = array(
           'message' => 'Property Type Updated Successfully.',
           'alert-type' => 'success',
        );

        return redirect()->route('all.type')->with($notification);
    }
    public function deleteType($id)
    {
        PropertyType::findOrFail($id)->delete();

        $notification = array(
           'message' => 'Property Type Deleted Successfully.',
           'alert-type' => 'success',
        );

        return redirect()->route('all.type')->with($notification);
    }




    ///////////// Amenitites All Method //////////////
    public function allAmenities()
    {
        $amenities = Amentities::get();
        return view('backend.amenities.all_amenities', compact('amenities'));
    }

    public function addAmenities()
    {
        return view('backend.amenities.add_amenities');
    }

   public function storeAmenities(Request $request)
   {
       Amentities::insert([

          'amenities_name' => $request->amenitis_name,
       ]);

       $notification = array(
          'message' => 'Amenities Create Successfully',
          'alert-type' => 'success'
       );

       return redirect()->route('all.amenities')->with($notification);

   }// End Method


   public function editAmenities($id)
   {

       $amenities = Amentities::findOrFail($id);
       return view('backend.amenities.edit_amenities', compact('amenities'));

   }// End Method


   public function updateAmenities(Request $request)
   {

       $ame_id = $request->id;

       Amentities::findOrFail($ame_id)->update([

          'amenities_name' => $request->amenitis_name,
       ]);

       $notification = array(
          'message' => 'Amenities Updated Successfully',
          'alert-type' => 'success'
       );

       return redirect()->route('all.amenities')->with($notification);

   }// End Method


   public function deleteAmenities($id)
   {

       Amentities::findOrFail($id)->delete();

       $notification = array(
          'message' => 'Amenities Deleted Successfully',
          'alert-type' => 'success'
       );

       return redirect()->back()->with($notification);

   }// End Method

   //////// Property Controller ////////
}