<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Amentities;
use App\Models\Facility;
use App\Models\MultiImage;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use PhpParser\Lexer\TokenEmulator\ExplicitOctalEmulator;
use PhpParser\Parser\Multiple;
use PHPUnit\Framework\Constraint\Count;

use function PHPSTORM_META\map;

class PropertyController extends Controller
{
    public function allProperty()
    {
        $property = Property::get();
        return view('backend.property.all_property', compact('property'));
    }


    public function addProperty()
    {
        $propertyType = PropertyType::get();
        $amenities = Amentities::get();
        $activeAgent = User::where('status', 'active')->where('role', 'agent')->get();
        return view('backend.property.add_property', compact('propertyType', 'amenities', 'activeAgent'));
    }

    public function storeProperty(Request $request)
    {
        $amen = $request->amenities_id;
        $amenities = implode(",", $amen);



        $image = $request->file('property_thambnail');
        $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
        Image::make($image)->resize(370, 250)->save('upload/property/thambnail/' . $name_gen);
        $save_url = 'upload/property/thambnail/' . $name_gen;

        $pcode = IdGenerator::generate(['table' => 'properties','field' => 'property_code','length' => 5, 'prefix' => 'PC' ]);

        $property_id = Property::insertGetId([
           'ptype_id' => $request->ptype_id,
           'amenities_id' => $amenities,
           'property_name' => $request->property_name,
           'property_slug' => strtolower(str_replace(' ', '-', $request->property_name)),
           'property_code' => $pcode,
           'property_status' => $request->property_status,

           'lowest_price' => $request->lowest_price,
           'max_price' => $request->max_price,
           'short_descp' => $request->short_descp,
           'long_descp' => $request->long_descp,
           'bedrooms' => $request->bedrooms,
           'bathrooms' => $request->bathrooms,
           'garage' => $request->garage,
           'garage_size' => $request->garage_size,

           'property_size' => $request->property_size,
           'property_video' => $request->property_video,
           'address' => $request->address,
           'city' => $request->city,
           'state' => $request->state,
           'postal_code' => $request->postal_code,

           'neighborhood' => $request->neighborhood,
           'latitude' => $request->latitude,
           'longtitude' => $request->longtitude,
           'featured' => $request->featured,
           'hot' => $request->hot,
           'agent_id' => $request->agent_id,
           'status' => 1,
           'property_thambnail' => $save_url,
           'created_at' => Carbon::now(),
        ]);

        // multi images upload from here
        $images = $request->file('multi_img');

        foreach($images as $img) {
            $make_name = hexdec(uniqid() . '.' . $img->getClientOriginalExtension());
            Image::make($img)->resize(770, 520)->save('upload/property/multi-image/' . $make_name);

            $uploadPath = 'upload/property/multi-images' . $make_name;
        }

        MultiImage::insert([
           'property_id' => $property_id,
           'photo_name' => $uploadPath,
           'created_at' => Carbon::now()
        ]);
        ;


        $facilities = Count($request->facility_name);

        if($facilities != null) {
            for($i = 0; $i < $facilities; $i++) {
                $fcount = new Facility();
                $fcount->property_id = $property_id;
                $fcount->facility_name = $request->facility_name[$i];
                $fcount->distance = $request->distance[$i];
                $fcount->save();
            }

        }

        $notification = array(
           'message' => 'Property Inserted Successfully',
           'alert-type' => 'success'
        );

        return redirect()->route('all.property')->with($notification);
    }

    public function editProperty($id)
    {

        $facilities = Facility::where('property_id', $id)->get();
        $multiImage = MultiImage::where('property_id', $id)->get();

        $property = Property::findOrFail($id);

        $type = $property->amenities_id;
        $property_ami = explode(',', $type);

        $propertytype = PropertyType::get();
        $amenities = Amentities::get();
        $activeAgent = User::where('status', 'active')->where('role', 'agent')->latest()->get();

        return view('backend.property.edit_property', compact('property', 'propertytype', 'amenities', 'activeAgent', 'property_ami', 'multiImage', 'facilities'));

    }

    public function updateProperty(Request $request)
    {

        $amen = $request->amenities_id;
        $amenites = implode(",", $amen);

        $property_id = $request->id;

        Property::findOrFail($property_id)->update([

            'ptype_id' => $request->ptype_id,
            'amenities_id' => $amenites,
            'property_name' => $request->property_name,
            'property_slug' => strtolower(str_replace(' ', '-', $request->property_name)),
            'property_status' => $request->property_status,

            'lowest_price' => $request->lowest_price,
            'max_price' => $request->max_price,
            'short_descp' => $request->short_descp,
            'long_descp' => $request->long_descp,
            'bedrooms' => $request->bedrooms,
            'bathrooms' => $request->bathrooms,
            'garage' => $request->garage,
            'garage_size' => $request->garage_size,

            'property_size' => $request->property_size,
            'property_video' => $request->property_video,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,

            'neighborhood' => $request->neighborhood,
            'latitude' => $request->latitude,
            'longtitude' => $request->longtitude,
            'featured' => $request->featured,
            'hot' => $request->hot,
            'agent_id' => $request->agent_id,
            'updated_at' => Carbon::now(),

        ]);

        $notification = array(
           'message' => 'Property Updated Successfully',
           'alert-type' => 'success'
      );

        return redirect()->route('all.property')->with($notification);
    }

  public function updatePropertyThambnail(Request $request)
  {
      $pro_id = $request->id;
      $oldImage = $request->old_img;

      $image = $request->file('property_thambnail');
      $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
      Image::make($image)->resize(370, 250)->save('upload/property/thambnail/' . $name_gen);
      $save_url = 'upload/property/thambnail/' . $name_gen;

      if(file_exists($oldImage)) {
          unlink($oldImage);
      }

      Property::findOrFail($pro_id)->update([
         'property_thambnail' => $save_url,
         'updated_at' => Carbon::now(),
      ]);

      $notification = array(
         'message' => 'Property Image Thambnail Updated Successfully',
         'alert-type' => 'success'
      );

      return redirect()->back()->with($notification);
  }


  public function updateProperMultiImage(Request $request)
  {
      $imgs = $request->multi_img;

      foreach($imgs as $id => $img) {
          $imgDel = MultiImage::findOrFail($id);
          unlink($imgDel);

          $make_name = hexdec(uniqid()).'.'.$img->getClientOriginalExtension();
          Image::make($img)->resize(770, 520)->save('upload/property/multi-image/'.$make_name);
          $uploadPath = 'upload/property/multi-image/'.$make_name;

          MultiImage::where('id', $id)->update([
             'photo_name' => $uploadPath,
             'updated_at' => Carbon::now(),
          ]);
      }


      $notification = array(
         'message' => 'Property Multi Image Updated Successfully',
         'alert-type' => 'success'
     );

      return redirect()->back()->with($notification);
  }

  public function deletePropertyMultiImage($id)
  {
      $oldImg = MultiImage::findOrFail($id);

      unlink($oldImg);

      MultiImage::findOrFail($id)->delete();

      $notification = array(
         'message' => 'Property Multi Image Deleted Successfully.',
         'alert-type' => 'Successs',
      );

      return redirect()->back()->with($notification);
  }

  public function storeNewMultiImage(Request $request)
  {

      $new_multi = $request->imageid;
      $image = $request->file('multi_img');

      $make_name = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
      Image::make($image)->resize(770, 520)->save('upload/property/multi-image/'.$make_name);
      $uploadPath = 'upload/property/multi-image/'.$make_name;

      MultiImage::insert([
         'property_id' => $new_multi,
         'photo_name' => $uploadPath,
         'created_at' => Carbon::now(),
      ]);

      $notification = array(
            'message' => 'Property Multi Image Added Successfully',
            'alert-type' => 'success'
         );

      return redirect()->back()->with($notification);
  }

   public function updatePropertyFacilities(Request $request)
   {
       $pid = $request->id;

       if($request->facility_name == null) {
           return redirect()->back();
       } else {
           Facility::where('property_id', $pid)->delete();

           $facilities = Count($request->facility_name);

           for ($i = 0; $i < $facilities; $i++) {
               $fcount = new Facility();
               $fcount->property_id = $pid;
               $fcount->facility_name = $request->facility_name[$i];
               $fcount->distance = $request->distance[$i];
               $fcount->save();
           }
       }

       $notification = array(
          'message' => 'Property Facility Updated Successfully',
          'alert-type' => 'success'
       );

       return redirect()->back()->with($notification);
   }

   public function deleteProperty($id)
   {
       $property= Property::findOrFail($id);
       unlink($property->property_thambnail);

       $property->delete();

       $imgs = MultiImage::where('property_id', $id)->get();
       foreach($imgs as $img) {
           unlink($img->photo_name);
       }
       MultiImage::where('property_id', $id)->delete();

       Facility::where('property_id', $id)->delete();

       $notification = array(
          'message' => 'Property Deleted Successfully',
          'alert-type' => 'success'
       );

       return redirect()->back()->with($notification);
   }




   ///// Details​៊៊៊៊
   public function detailsProperty($id)
   {
       $facilities = Facility::where('property_id', $id)->get();
       $property = Property::findOrFail($id);

       $type = $property->amenities_id;
       $property_ami = explode(',', $type);

       $multiImage = MultiImage::where('property_id', $id)->get();

       $propertytype = PropertyType::latest()->get();
       $amenities = Amentities::latest()->get();
       $activeAgent = User::where('status', 'active')->where('role', 'agent')->get();

       return view('backend.property.details_property', compact('property', 'propertytype', 'amenities', 'activeAgent', 'property_ami', 'multiImage', 'facilities'));
   }

   public function InactiveProperty(Request $request)
   {

       $pid = $request->id;
       Property::findOrFail($pid)->update([

           'status' => 0,

       ]);

       $notification = array(
             'message' => 'Property Inactive Successfully',
             'alert-type' => 'success'
         );

       return redirect()->route('all.property')->with($notification);


   }


   public function ActiveProperty(Request $request)
   {

       $pid = $request->id;
       Property::findOrFail($pid)->update([

          'status' => 1,

       ]);

       $notification = array(
             'message' => 'Property Active Successfully',
             'alert-type' => 'success'
          );

       return redirect()->route('all.property')->with($notification);
   }
}