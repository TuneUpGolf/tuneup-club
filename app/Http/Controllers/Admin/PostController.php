<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PostController extends Controller
{

    public function getAllPosts()
    {
        try {
            if (Auth::user()->can('manage-posts')) {
                $posts = Post::with('instructor')->paginate(request()->get('per_page', 10));
                return response()->json(['posts' => $posts], 200);
            } else {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching posts.', 'message' => $e->getMessage()], 500);
        }
    }

    public function getInstructorPosts(Request $request)
    {
        try {
            $this->validate($request, [
                'influencer_id' => 'required',
            ]);

            if (Auth::user()->can('manage-posts')) {
                $posts = Post::with('instructor')->where('influencer_id', $request?->influencer_id)->paginate(request()->get('per_page', 10));
                return response()->json(['posts' => $posts], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching posts.', 'message' => $e->getMessage()], 500);
        }
    }

    public function createPost(Request $request)
    {
        try {
            $this->validate($request, [
                'post_text' => 'required|string',
                'post_image' => 'nullable|string',
                'post_video' => 'nullable|string',
                'paid' => 'required|boolean',
                'price' => 'nullable|numeric',
            ]);

            if (Auth::user()->can('create-posts')) {
                $post = Post::create([
                    'tenant_id'     => Auth::user()->tenant_id,
                    'influencer_id' => Auth::user()->id,
                    'post_text'     => $request?->post_text,
                    'paid'          => $request?->paid,
                    'price'         => $request?->price,
                ]);
                if ($request->hasfile('video')) {
                    $post['post_video'] = $request->file('video')->store('post');
                }
                if ($request->hasfile('image')) {
                    $post['post_image'] = $request->file('image')->store('post');
                }
                $post->update();
                return response()->json(['post' => $post], 200);
            }
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error creating post.', 'message' => $e->getMessage()], 500);
        }
    }
}
