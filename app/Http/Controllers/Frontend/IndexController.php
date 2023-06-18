<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\MultiImage;
use App\Models\Property;
use App\Models\PropertyMessage;
use App\Models\PropertyType;
use App\Models\State;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{
    public function propertyDetails($id, $slug)
    {
        $property = Property::findOrFind($id);
        $multiImage = MultiImage::where('property_id', $id)->get();

        $amenities = $property->amenities_id;
        $property_amen = explode(',', $amenities);

        $facility = Facility::where('property_id', $id)->get();

        $type_id = $property->ptype_id;
        $relatedProperty = Property::where('ptype_id', $type_id)->where('id', '!=', $id)->orderBy('id', 'DESC')->limit(3)->get();

        return view('frontend.property.property_details', compact('property', 'multiImage', 'property_amen', 'facility', 'relatedProperty'));
    }

    public function propertyMessage(Request $request)
    {
        $pid = $request->property_id;
        $aid = $request->agent_id;

        if(Auth::check()) {
            PropertyMessage::insert([
               'user_id' => Auth::user()->id,
               'agent_id' => $aid,
               'property_id' => $pid,
               'msg_name' => $request->msg_name,
               'msg_email' => $request->msg_email,
               'msg_phone' => $request->msg_phone,
               'message' => $request->message,
               'created_at' => Carbon::now(),
            ]);

            $notification = array(
               'message' => 'Send Message Successfully.',
               'alert-type' => 'success',
            );

            return redirect()->back()->with($notification);
        } else {
            $notification = array(
               'message' => 'Please Login your account first.',
               'alert-type' => 'error',
            );

            return redirect()->back()->with($notification);
        }
    }

    public function agentDetails($id)
    {
        $agent = User::findOrFail($id);
        $property = Property::where('agent_id', $id)->get();
        $featured = Property::where('featured', '1')->limit(3)->get();

        $rentproperty = Property::where('proptery_status', 'rent')->get();
        $buyproperty = Property::where('property_status', 'buy')->get();

        return view('frontend.agent.agent_details', compact('agent', 'property', 'featured', 'rentproperty', 'buyproperty'));
    }

    public function agentDetailsMessage(Request $request)
    {
        $aid = $request->agent_id;

        if(Auth::check()) {
            PropertyMessage::insert([
               'user_id' => Auth::user()->id,
               'agent_id' => $aid,
               'msg_name' => $request->msg_name,
               'msg_email' => $request->email,
               'msg_phone' => $request->phone,
               'message' => $request->message,
               'created_at' => Carbon::now(),
            ]);

            $notification = array(
               'message' => 'Send Message Successfully.',
               'alert-type' => 'success',
            );

            return redirect()->back()->with($notification);
        } else {
            $notification = array(
               'message' => 'Please Login Your Account First.',
               'alert-type' => 'error',
            );

            return redirect()->back()->with($notification);
        }
    }


    public function rentProperty()
    {
        $property = Property::where('status', '1')->where('proptery_status', 'rent')->paginate(3);
        return view('frontend.property.rent_property', compact('property'));
    }

    public function buyProperty()
    {
        $property = Property::where('status', '1')->where('property_status', 'buy')->get();
        return view('frontend.property.buy_property', compact('property'));
    }

    public function propertyType($id)
    {
        $property = Property::where('status', '1')->where('ptype_id', $id)->get();
        $pbread = PropertyType::where('id', $id)->first();
        return view('frontend.property.property_type', compact('property', 'pbread'));
    }

    public function stateDetails($id)
    {
        $property = Property::where('status', '1')->where('state', $id)->get();

        $bstate = State::where('id', $id)->first();
        return view('frontend.property.state_property', compact('property', 'bstate'));
    }

    public function buyPropertySearch(Request $request)
    {
        $request->validate(['search' => 'required']);
        $item = $request->search;
        $sstate = $request->state;
        $stype = $request->ptype_id;

        $property = Property::where('property_name', 'like', '%' . $item . '%')
                    ->where('property_status', 'buy')->with('type', 'pstate')
                    ->whereHas('pstate', function ($q) use ($sstate) {
                        $q->where('state_name', 'like', '%' . $sstate . '%');
                    })
                    ->whereHas('type', function ($q) use ($stype) {
                        $q->where('type_name', 'like', '%' . $stype . '%');
                    })
                    ->get();
        return view('frontend.property.property_search', compact('property'));
    }

    public function rentPropertySearch(Request $request)
    {
        $request->validate(['search' => 'requried']);
        $item = $request->search;
        $sstate = $request->state;
        $stype = $request->ptype_id;

        $property = Property::where('property_name', 'like', '%' .$item. '%')
                            ->where('property_status', 'rent')->with('type', 'pstate')
                            ->whereHas('pstate', function ($q) use ($sstate) {
                                $q->where('state_name', 'like', '%' .$sstate. '%');
                            })
                            ->whereHas('type', function ($q) use ($stype) {
                                $q->where('type_name', 'like', '%' .$stype. '%');
                            })
                            ->get();

        return view('frontend.property.property_search', compact('property'));
    }

    public function allPropertySearch(Request $request)
    {
        $property_status = $request->property_status;
        $stype = $request->ptype_id;
        $sstate = $request->state;
        $bedrooms = $request->bedrooms;
        $bathrooms = $request->bathrooms;


        $property = Property::where('status', '1')
                            ->where('bedrooms', $bedrooms)
                            ->where('bathrooms', 'like', '%' . $bathrooms . '%')
                            ->where('property_status', $property_status)
                            ->whereHas('pstate', function ($q) use ($sstate) {
                                $q->where('state_name', 'like', '%' . $sstate . '%');
                            })
                           ->whereHas('type', function ($q) use ($stype) {
                               $q->where('type_name', 'like', '%' . $stype . '%');
                           })
                           ->get();


        return view('frontend.property.property_search', compact('property'));
    }
}