<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Exists;
use PhpParser\Node\Expr\FuncCall;
use SebastianBergmann\CodeUnit\FunctionUnit;

class WishlistController extends Controller
{
    public function addToWishList(Request $request, $property_id)
    {
        if(Auth::check()) {
            $exists = Wishlist::where('user_id', Auth::id())->where('property_id', $property_id)->first();

            if(!$exists) {
                Wishlist::insert([
                   'user_id' => Auth::id(),
                   'property_id' => $property_id,
                   'created_at' => Carbon::now(),
                ]);
                return response()->json(['success' => 'Successfully Added On Your Wishlist']);
            } else {
                return response()->json(['error' => 'This property Has Already in your wishlist.']);
            }
        }
    }

    public function userWishList()
    {
        $id = Auth::user()->id;
        $userData = User::find($id);
        return view('frontend.dashboard.wishlist', compact('userData'));
    }

    public function getWishlistProperty()
    {
        $wishlist = Wishlist::with('property')->where('user_id', Auth::user()->id)->get();

        $wishQty = wishList::count();

        return response()->json(['wishlist' => $wishlist, 'wishQty' => $wishQty]);
    }

    public function wishListRemove($id)
    {
        Wishlist::where('user_id', Auth::id())->where('id', $id)->delete();
        return response()->json(['success' => 'Successfully Property Remove.']);
    }
}