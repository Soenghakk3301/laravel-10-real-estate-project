<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        return view('frontend.index');
    }

    public function userProfile()
    {
        $id = Auth::user()->id;
        $userData = User::find($id);
        return view('frontend.dashboard.edit_profile', compact('userData'));
    }

    public function userProfileStore(Request $request)
    {
        $id = Auth::user()->id;
        $data = User::find($id);

        $data->username = $request->username;
        $data->name = $request->name;
        $data->email = $request->email;
        $data->phone = $request->phone;
        $data->address = $request->address;

        if($request->file('photo')) {
            $file = $request->file('photo');
            @unlink(public_path('upload/user_images/' . $data->photo));
            $filename = date('YmdHi').$file->getClientOriginalExtension();
            $file->move(public_path('upload/user_images'), $filename);
            $data->photo = $filename;
        }

        $data->save();

        $notification = array(
           'message' => 'User Profile Updated Successfully.',
           'alert-type' => 'Success',
        );


        return redirect()->back()->with($notification);
    }


    public function userLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();


        $notification = array(
           'message' => 'User Logout Successfully.',
           'alert-type' => 'success',
        );

        return redirect('/login')->with($notification);
    }

    public function userChangePassword()
    {

        return view('frontend.dashboard.change_password');

    }


    public function userPasswordUpdate(Request $request)
    {

        // Validation
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|confirmed'

        ]);

        /// Match The Old Password

        if (!Hash::check($request->old_password, auth::user()->password)) {

            $notification = array(
             'message' => 'Old Password Does not Match!',
             'alert-type' => 'error'
      );

            return back()->with($notification);
        }

        /// Update The New Password

        User::whereId(auth()->user()->id)->update([
            'password' => Hash::make($request->new_password)

        ]);

        $notification = array(
           'message' => 'Password Change Successfully',
           'alert-type' => 'success'
      );

        return back()->with($notification);

    }

    public function userScheduleRequest()
    {
        $id = Auth::user()->id;
        $userData = User::find($id);

        $srequest = Schedule::where('user_id', $id)->get();
        return view('frontend.message.schedule_request', compact('userData', 'srequest'));
    }

    public function liveChat()
    {

        $id = Auth::user()->id;
        $userData = User::find($id);
        return view('frontend.dashboard.live_chat', compact('userData'));
    }
}