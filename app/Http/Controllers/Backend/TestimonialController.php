<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Testimonials;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class TestimonialController extends Controller
{
    public function allTestimonials()
    {
        $testimonial = Testimonials::get();
        return view('backend.testimonial.all_testimonial', compact('testimonial'));
    }

    public function addTestimonials()
    {
        return view('backend.testimonial.add_testimonials');
    }

    public function storeTestimonials(Request $request)
    {
        $image = $request->file('image');
        $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
        Image::make($image)->resize(100, 100)->save('upload/testimonial/' . $name_gen);
        $save_url = 'upload/testimonial/'.$name_gen;

        Testimonials::insert([
         'name' => $request->name,
         'position' => $request->position,
         'message' => $request->message,
         'image' => $save_url,
        ]);


        $notification = array(
         'message' => 'Testimonial Inserted Successfully',
         'alert-type' => 'success'
         );

        return redirect()->route('all.testimonials')->with($notification);
    }

    public function editTestimonials($id)
    {
        $testimonial = Testimonials::findOrFail($id);
        return view('backend.testimonial.edit_testimonial', compact('testimonial'));
    }

    public function updateTestimonials(Request $request)
    {
        $test_id = $request->id;

        if($request->fill('image')) {
            $image = $request->file('image');
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            Image::make($image)->resize(100, 100)->save('upload/testimonial/' . $name_gen);
            $save_url = 'upload/testimonial/'.$name_gen;

            Testimonials::findOrFail($test_id)->update([
                'name' => $request->name,
                'position' => $request->position,
                'message' => $request->message,
                'image' => $save_url,
            ]);

            $notification = array(
               'message' => 'Testimonial Updated Successfully',
               'alert-type' => 'success'
            );

            return redirect()->route('all.testimonials')->with($notification);
        } else {
            Testimonials::findOrFail($test_id)->update([
               'name' => $request->name,
               'position' => $request->position,
               'message' => $request->message,
            ]);

            $notification = array(
                   'message' => 'Testimonial Updated Successfully',
                   'alert-type' => 'success'
               );

            return redirect()->route('all.testimonials')->with($notification);
        }
    }

    public function deleteTestimonials($id)
    {
        $test = Testimonials::findOrFail($id);
        $img = $test->image;
        unlink($img);

        Testimonials::findOrFail($id)->delete();

        $notification = array(
           'message' => 'Testimonial Deleted Successfully',
           'alert-type' => 'success'
      );

        return redirect()->back()->with($notification);
    }
}