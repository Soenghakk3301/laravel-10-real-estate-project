<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Comment;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;

class BlogController extends Controller
{
    public function allBlogCategory()
    {
        $category = BlogCategory::get();
        return view('backend.category.blog_category', compact('category'));
    }

    public function storeBlogCategory(Request $request)
    {
        BlogCategory::insert([
           'category_name' => $request->category_name,
           'category_slug' => strtolower(str_replace(' ', '-', $request->category_name)),
        ]);

        $notification = array(
         'message' => 'BlogCategory Create Successfully',
         'alert-type' => 'success'
        );

        return redirect()->route('all.blog.category')->with($notification);
    }

    public function editBlogCategory($id)
    {
        $categories = BlogCategory::findOrFail($id);
        return response()->json($categories);
    }

    public function deleteBlogCategory($id)
    {
        BlogCategory::findOrFail($id)->delete();

        $notification = array(
           'message' => 'BlogCategory deleted successfully.',
           'alert-type' => 'success',
        );

        return redirect()->back()->with($notification);
    }

    public function allPost()
    {
        $post = BlogPost::get();
        return view('backend.post.all_post', compact('post'));
    }

    public function addPost()
    {
        $blogcat = BlogCategory::get();
        return view('backend.post.add_post', compact('blogcat'));
    }

    public function storePost(Request $request)
    {
        $image = $request->file('post_image');
        $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
        Image::make($image)->resize(370, 250)->save('upload/post/' . $name_gen);

        $save_url = 'upload/post/' . $name_gen;

        BlogPost::insert([
         'blogcat_id' => $request->blogcat_id,
         'user_id' => Auth::user()->id,
         'post_title' => $request->post_title,
         'post_slug' => strtolower(str_replace(' ', '-', $request->post_title)),
         'short_descp' => $request->short_descp,
         'long_descp' => $request->long_descp,
         'post_tags' => $request->post_tags,
         'post_image' => $save_url,
         'created_at' => Carbon::now(),
         ]);

        $notification = array(
           'message' => 'BlogPost Inserted Successfully.',
           'alert-type' => 'success',
        );

        return redirect()->route('all.post')->with($notification);
    }

    public function editPost($id)
    {
        $blogcat = BlogCategory::get();
        $post = BlogPost::findOrFail($id);

        return view('backend.post.edit_post', compact('post', 'blogcat'));
    }

    public function updatePost(Request $request)
    {
        $post_id = $request->id;

        if ($request->file('post_image')) {

            $image = $request->file('post_image');
            $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
            Image::make($image)->resize(370, 250)->save('upload/post/'.$name_gen);
            $save_url = 'upload/post/'.$name_gen;

            BlogPost::findOrFail($post_id)->update([
                'blogcat_id' => $request->blogcat_id,
                'user_id' => Auth::user()->id,
                'post_title' => $request->post_title,
                'post_slug' => strtolower(str_replace(' ', '-', $request->post_title)),
                'short_descp' => $request->short_descp,
                'long_descp' => $request->long_descp,
                'post_tags' => $request->post_tags,
                'post_image' => $save_url,
                'created_at' => Carbon::now(),
            ]);

            $notification = array(
                   'message' => 'BlogPost Updated Successfully',
                   'alert-type' => 'success'
               );

            return redirect()->route('all.post')->with($notification);

        } else {

            BlogPost::findOrFail($post_id)->update([
              'blogcat_id' => $request->blogcat_id,
              'user_id' => Auth::user()->id,
              'post_title' => $request->post_title,
              'post_slug' => strtolower(str_replace(' ', '-', $request->post_title)),
              'short_descp' => $request->short_descp,
              'long_descp' => $request->long_descp,
              'post_tags' => $request->post_tags,
              'created_at' => Carbon::now(),
            ]);

            $notification = array(
                   'message' => 'BlogPost Updated Successfully',
                   'alert-type' => 'success'
               );

            return redirect()->route('all.post')->with($notification);

        }
    }

    public function deletePost($id)
    {
        $post = BlogPost::findOrFail($id);
        $img = $post->post_image;
        unlink($img);

        BlogPost::findOrFail($id)->delete();

        $notification = array(
           'message' => 'BlogPost Deleted Successfully',
           'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }

    public function blogDetails($slug)
    {
        $blog = BlogPost::where('post_slug', $slug)->first();

        $tags = $blog->post_tags;
        $tags_all = explode(',', $tags);

        $bcategory = BlogCategory::latest()->get();
        $dpost = BlogPost::latest()->limit(3)->get();

        return view('frontend.blog.blog_details', compact('blog', 'tags_all', 'bcategory', 'dpost'));
    }

    public function blogCatList($id)
    {
        $blog = BlogPost::where('blogcat_id', $id)->get();
        $breadcat = BlogCategory::where('id', $id)->first();
        $bcategory = BlogCategory::latest()->get();
        $dpost = BlogPost::latest()->limit(3)->get();

        return view('frontend.blog.blog_cat_list', compact('blog', 'breadcat', 'bcategory', 'dpost'));
    }

    public function blogList()
    {
        $blog = BlogPost::latest()->get();
        $bcategory = BlogCategory::latest()->get();
        $dpost = BlogPost::latest()->limit(3)->get();

        return view('frontend.blog.blog_list', compact('blog', 'bcategory', 'dpost'));
    }

    public function storeComment(Request $request)
    {
        $pid = $request->post_id;

        Comment::insert([
            'user_id' => Auth::user()->id,
            'post_id' => $pid,
            'parent_id' => null,
            'subject' => $request->subject,
            'message' => $request->message,
            'created_at' => Carbon::now(),

        ]);

        $notification = array(
          'message' => 'Comment Inserted Successfully',
          'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }

    public function adminBlogComment()
    {
        $comment = Comment::where('parent_id', null)->latest()->get();
        return view('backend.comment.comment_all', compact('comment'));
    }

    public function adminCommentReply($id)
    {
        $comment = Comment::where('id', $id)->first();
        return view('backend.comment.reply_comment', compact('comment'));
    }

    public function replyMessage(Request $request)
    {
        $id = $request->id;
        $user_id = $request->user_id;
        $post_id = $request->post_id;

        Comment::insert([
            'user_id' => $user_id,
            'post_id' => $post_id,
            'parent_id' => $id,
            'subject' => $request->subject,
            'message' => $request->message,
            'created_at' => Carbon::now(),

        ]);

        $notification = array(
          'message' => 'Reply Inserted Successfully',
          'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }

    public function storeSchedule(Request $request)
    {
        $aid = $request->agent_id;
        $pid = $request->property_id;

        if (Auth::check()) {

            Schedule::insert([

                'user_id' => Auth::user()->id,
                'property_id' => $pid,
                'agent_id' => $aid,
                'tour_date' => $request->tour_date,
                'tour_time' => $request->tour_time,
                'message' => $request->message,
                'created_at' => Carbon::now(),
            ]);

            $notification = array(
               'message' => 'Send Request Successfully',
               'alert-type' => 'success'
            );

            return redirect()->back()->with($notification);


        } else {

            $notification = array(
               'message' => 'Plz Login Your Account First',
               'alert-type' => 'error'
            );

            return redirect()->back()->with($notification);

        }
    }
}