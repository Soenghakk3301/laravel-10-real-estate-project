<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function AdminDashboard()
    {
        return view('admin.index');
    }

    public function adminLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $notification = array(
         'message' => 'Admin Logout Successfully',
         'alert-type' => 'success'
        );

        return redirect('/admin/login')->with($notification);
    }

    public function adminLogin()
    {
        return view('admin.admin_login');
    }

    public function adminProfile()
    {
        $profile = User::find(Auth::user()->id);
        return view('admin.admin_profile_view', compact('profile'));
    }


    public function adminProfileStore(Request $request)
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
            @unlink(public_path('upload/admin_images/' . $data->photo));
            $filename = date('YmdHi').$file->getClientOriginalExtension();
            $file->move(public_path('upload/admin_images'), $filename);
            $data->photo = $filename;
        }

        $data->save();


        $notification = array(
           'message' => 'Admin Profile Updated Successfully.',
           'alert-type' => 'success',
        );

        return redirect()->back()->with($notification);
    }

    public function adminChangePassword()
    {
        $id = Auth::user()->id;
        $profile = User::find($id);

        return view('admin.admin_change_password', compact('profile'));
    }

    public function adminUpdatePassword(Request $request)
    {
        // validation
        $request->validate(
            [
              'old_password' => 'required',
              'new_password' => 'required|confirmed',
         ]
        );

        // match the old password
        if(!Hash::check($request->old_password, auth::user()->password)) {
            $notification = array(
               'message' => 'Old password does not Match!',
               'alert-type' => 'error',
            );

            return back()->with($notification);
        }

        // update new password
        User::whereId(auth()->user()->id)->update([
              'password' => Hash::make($request->new_password)
        ]);

        // message
        $notification = array(
           'message' => 'Password Change Successfully',
           'alert-type' => 'success'
        );

        return back()->with($notification);
    }



    //////// Agent User All Method //////////

    public function allAgent()
    {
        $allagent = User::where('role', 'agent')
                        ->get();
        return view('backend.agentuser.all_agent', compact('allagent'));
    }

    public function addAgent()
    {
        return view('backend.agentuser.add_agent');
    }

    public function storeAgent(Request $request)
    {
        User::insert([
           'name' => $request->name,
           'email' => $request->email,
           'phone' => $request->phone,
           'address' => $request->address,
           'password' => Hash::make($request->password),
           'role' => 'agent',
           'status' => 'success',
        ]);

        $notification = array(
         'message' => 'Agent Created Successfully.',
         'alert-type' => 'success',
        );

        return redirect()->route('all.agent')->with($notification);
    }

    public function editAgent($id)
    {
        $agent = User::findOrFail($id);
        return view('backend.agentuser.edit_agent', compact('agent'));
    }

    public function updateAgent(Request $request)
    {
        $user_id = $request->id;

        User::findOrFail($user_id)->update([
           'name' => $request->name,
           'email' => $request->email,
           'phone' => $request->phone,
           'address' => $request->address,
        ]);

        $notification = array(
           'message' => 'Agent Updated Successfully',
           'alert-type' => 'success'
        );

        return redirect()->route('all.agent')->with($notification);
    }

    public function deleteAgent($id)
    {

        User::findOrFail($id)->delete();

        $notification = array(
               'message' => 'Agent Deleted Successfully',
               'alert-type' => 'success'
           );

        return redirect()->back()->with($notification);

    }
}