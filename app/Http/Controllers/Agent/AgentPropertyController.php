<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Amentities;
use App\Models\Facility;
use App\Models\MultiImage;
use App\Models\PackagePlan;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use Carbon\Carbon;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Barryvdh\DomPDF\Facade\Pdf;

class AgentPropertyController extends Controller
{
    public function agentAllProperty()
    {
        $id = Auth::user()->id;
        $property = Property::where('agent_id', $id)->get();
        return view('agent.property.all_property', compact('property'));
    }

    public function agentAddProperty()
    {
        $amen = PropertyType::get();
        $amenities = Amentities::get();

        $id = Auth::user()->id;
        $property = User::where('role', 'agent')->where('id', $id)->first();
        $pcount = $property->credit;

        if($pcount == 1) {
            return redirect()->route('buy.package');
        } else {
            return  view('agent.property.add_property', compact('propertytype', 'amenities'));
        }


        return view('agent.property.add_property', compact('amen', 'amenities'));
    }

    public function agentStoreProperty(Request $request)
    {

        $id = Auth::user()->id;
        $uid = User::findOrFail($id);
        $nid = $uid->credit;

        $amen = $request->amenities_id;
        $amenites = implode(",", $amen);

        $pcode = IdGenerator::generate(['table' => 'properties', 'field' => 'property_code', 'length' => 5, 'prefix' => 'PC']);

        $image = $request->file('property_thambnail');
        $name_gen = hexdec(uniqid() . '.' . $image->getClientOriginalExtension());
        Image::make($image)->resize(370, 250)->save('upload/property/thambnail' . $name_gen);
        $save_url = 'upload/property/thamnail' . $name_gen;


        $property_id = Property::insertGetId([

                'ptype_id' => $request->ptype_id,
                'amenities_id' => $amenites,
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
                'longitude' => $request->longitude,
                'featured' => $request->featured,
                'hot' => $request->hot,
                'agent_id' => Auth::user()->id,
                'status' => 1,
                'property_thambnail' => $save_url,
                'created_at' => Carbon::now(),
            ]);

        /// Multiple Image Upload From Here ////

        $images = $request->file('multi_img');
        foreach($images as $img) {

            $make_name = hexdec(uniqid()).'.'.$img->getClientOriginalExtension();
            Image::make($img)->resize(770, 520)->save('upload/property/multi-image/'.$make_name);
            $uploadPath = 'upload/property/multi-image/'.$make_name;

            MultiImage::insert([

                'property_id' => $property_id,
                'photo_name' => $uploadPath,
                'created_at' => Carbon::now(),

            ]);
        } // End Foreach

        /// End Multiple Image Upload From Here ////

        /// Facilities Add From Here ////

        $facilities = Count($request->facility_name);

        if ($facilities != null) {
            for ($i=0; $i < $facilities; $i++) {
                $fcount = new Facility();
                $fcount->property_id = $property_id;
                $fcount->facility_name = $request->facility_name[$i];
                $fcount->distance = $request->distance[$i];
                $fcount->save();
            }
        }

        /// End Facilities  ////


        User::where('id', $id)->update([
         'credit' => DB::raw('1 + '.$nid),
        ]);

        $notification = array(
        'message' => 'Property Inserted Successfully',
        'alert-type' => 'success'
        );

        return redirect()->route('agent.all.property')->with($notification);

    }


    public function agentEditProperty($id)
    {
        $facilities = Facility::where('property_id', $id)->get();
        $property = Property::findOrFail($id);

        $type = $property->amenities_id;
        $property_ami = explode(',', $type);

        $multiImage = MultiImage::where('property_id', $id)->get();

        $propertytype = PropertyType::get();
        $amenities = Amentities::get();

        return view('agent.property.edit_property', compact('property', 'propertytype', 'property_ami', 'multiImage', 'facilities'));
    }

    public function agentUpdateProperty(Request $request)
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
            'longitude' => $request->longitude,
            'featured' => $request->featured,
            'hot' => $request->hot,
            'agent_id' => Auth::user()->id,
            'updated_at' => Carbon::now(),

        ]);

        $notification = array(
           'message' => 'Property Updated Successfully',
           'alert-type' => 'success'
        );

        return redirect()->route('agent.all.property')->with($notification);
    }


    public function agentUpdatePropertythambnail(Request $request)
    {
        $pro_id = $request->id;
        $oldImage = $request->old_img;

        $image = $request->file('property_thambnail');
        $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
        Image::make($image)->resize(370, 250)->save('upload/property/thambnail/'.$name_gen);
        $save_url = 'upload/property/thambnail/'.$name_gen;

        if (file_exists($oldImage)) {
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

    public function agentUpdatePropertyMultiImage(Request $request)
    {
        $imgs = $request->multi_img;

        foreach($imgs as $id => $img) {
            $imgDel = MultiImage::findOrFail($id);
            unlink($imgDel->photo_name);

            $make_name = hexdec(uniqid()).'.'.$img->getClientOriginalExtension();
            Image::make($img)->resize(770, 520)->save('upload/property/multi-image/'.$make_name);
            $uploadPath = 'upload/property/multi-image/'.$make_name;

            MultiImage::where('id', $id)->update([

                'photo_name' => $uploadPath,
                'updated_at' => Carbon::now(),

            ]);

        } // End Foreach


        $notification = array(
           'message' => 'Property Multi Image Updated Successfully',
           'alert-type' => 'success'
      );

        return redirect()->back()->with($notification);
    }

    public function agentPropertyMultiimgDelete($id)
    {

        $oldImg = MultiImage::findOrFail($id);
        unlink($oldImg->photo_name);

        MultiImage::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Property Multi Image Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);

    }


    public function agentStoreNewMultiimage(Request $request)
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


    public function agentUpdatePropertyFacilities(Request $request)
    {

        $pid = $request->id;

        if ($request->facility_name == null) {
            return redirect()->back();
        } else {
            Facility::where('property_id', $pid)->delete();

            $facilities = Count($request->facility_name);

            for ($i=0; $i < $facilities; $i++) {
                $fcount = new Facility();
                $fcount->property_id = $pid;
                $fcount->facility_name = $request->facility_name[$i];
                $fcount->distance = $request->distance[$i];
                $fcount->save();
            } // end for
        }

        $notification = array(
           'message' => 'Property Facility Updated Successfully',
           'alert-type' => 'success'
      );

        return redirect()->back()->with($notification);
    }


    /////////// Buy Package ///////////
    public function buyPackage()
    {
        return view('agent.package.buy_package');
    }

    public function buyBusinessPlan()
    {
        $id = Auth::user()->id;
        $data = User::find($id);
        return view('agent.package.business_plan', compact('user'));
    }


    public function storeBusinessPlan(Request $request)
    {
        $id = Auth::user()->id;
        $uid = User::findOrFail($id);
        $nid = $uid->credit;

        PackagePlan::insert([

           'user_id' => $id,
           'package_name' => 'Business',
           'package_credits' => '3',
           'invoice' => 'ERS'.mt_rand(10000000, 99999999),
           'package_amount' => '20',
           'created_at' => Carbon::now(),
         ]);

        User::where('id', $id)->update([
           'credit' => DB::raw('3 + '.$nid),
        ]);

        $notification = array(
         'message' => 'You have purchase Basic Package Successfully',
         'alert-type' => 'success'
         );

        return redirect()->route('agent.all.property')->with($notification);

    }

    public function buyProfessionalPlan()
    {

        $id = Auth::user()->id;
        $data = User::find($id);
        return view('agent.package.professional_plan', compact('data'));

    }// End Method


   public function storeProfessionalPlan(Request $request)
   {

       $id = Auth::user()->id;
       $uid = User::findOrFail($id);
       $nid = $uid->credit;

       PackagePlan::insert([

         'user_id' => $id,
         'package_name' => 'Professional',
         'package_credits' => '10',
         'invoice' => 'ERS'.mt_rand(10000000, 99999999),
         'package_amount' => '50',
         'created_at' => Carbon::now(),
       ]);

       User::where('id', $id)->update([
           'credit' => DB::raw('10 + '.$nid),
       ]);



       $notification = array(
            'message' => 'You have purchase Professional Package Successfully',
            'alert-type' => 'success'
        );

       return redirect()->route('agent.all.property')->with($notification);
   }

   public function PackageHistory()
   {

       $id = Auth::user()->id;
       $packagehistory = PackagePlan::where('user_id', $id)->get();
       return view('agent.package.package_history', compact('packagehistory'));

   }


   public function agentPackageInvoice($id)
   {

       $packagehistory = PackagePlan::where('id', $id)->first();

       $pdf = Pdf::loadView('agent.package.package_history_invoice', compact('packagehistory'))->setPaper('a4')->setOption([
           'tempDir' => public_path(),
           'chroot' => public_path(),
       ]);
       return $pdf->download('invoice.pdf');

   }

   public function adminPackageHistory()
   {
       $packagehistory = PackagePlan::get();
       return view('backend.package.package_history', compact('packagehistory'));
   }

   public function packageInvoice($id)
   {
       $packagehistory = PackagePlan::where('id', $id)->first();

       $pdf = Pdf::loadView('backend.package.package_history_invoice', compact('packagehistory'))->setPaper('a4')->setOption([
          'tempDir' => public_path(),
          'chroot' => public_path(),
       ]);

       return $pdf->download('invoice.pdf');
   }
}