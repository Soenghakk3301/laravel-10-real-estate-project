<?php

namespace App\Http\Controllers;

use App\Models\PropertyMessage;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AgentController extends Controller
{
    public function agentDashboard()
    {
        return view('agent.index');
    }


    public function agentLogin()
    {

        return view('agent.agent_login');

    }

    public function agentRegister(Request $request)
    {


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'agent',
            'status' => 'inactive',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::AGENT);

    }

  public function agentLogout(Request $request)
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


  public function agentProfile()
  {

      $id = Auth::user()->id;
      $profileData = User::find($id);
      return view('agent.agent_profile_view', compact('profileData'));

  }

  public function agentProfileStore(Request $request)
  {

      $id = Auth::user()->id;
      $data = User::find($id);
      $data->username = $request->username;
      $data->name = $request->name;
      $data->email = $request->email;
      $data->phone = $request->phone;
      $data->address = $request->address;

      if ($request->file('photo')) {
          $file = $request->file('photo');
          @unlink(public_path('upload/agent_images/'.$data->photo));
          $filename = date('YmdHi').$file->getClientOriginalName();
          $file->move(public_path('upload/agent_images'), $filename);
          $data['photo'] = $filename;
      }

      $data->save();

      $notification = array(
          'message' => 'Agent Profile Updated Successfully',
          'alert-type' => 'success'
      );

      return redirect()->back()->with($notification);

  }


   public function AgentChangePassword()
   {

       $id = Auth::user()->id;
       $profileData = User::find($id);
       return view('agent.agent_change_password', compact('profileData'));

   }// End Method


  public function AgentUpdatePassword(Request $request)
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


  public function agentPropertyMessage($id)
  {
      $id = Auth::user()->id;
      $usermsg = PropertyMessage::where('agent_id', $id)->get();

      return view('agent.message.all_message', compact('usermsg'));
  }

  public function agentMessageDetails($id)
  {
      $uid = Auth::user()->id;
      $usermsg = PropertyMessage::where('agent_id', $uid)->get();

      $msgdetails = PropertyMessage::findOrFind($id);

      return view('agent.message.message_details', compact('usermsg', 'msgdetails'));
  }
}