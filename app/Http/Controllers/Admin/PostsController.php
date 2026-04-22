<?php

namespace App\Http\Controllers\Admin;

use App\Models\Plan;
use App\Models\Post;
use App\Models\Role;
use App\Models\Album;
use App\Models\Category;
use App\Models\LikePost;
use App\Models\ReportPost;
use Illuminate\Support\Str;
use App\Models\PurchasePost;
use Illuminate\Http\Request;
use App\Models\AlbumCategory;
use App\Facades\UtilityFacades;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\DataTables\Admin\PostDataTable;
use App\Http\Resources\PostAPIResource;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\ValidationException;
use App\DataTables\Admin\ReportedPostDataTable;
use Illuminate\Validation\UnauthorizedException;

class PostsController extends Controller
{
    public function index(PostDataTable $dataTable)
    {
        if (Auth::user()->can('manage-blog')) {
            $isInfluencer = Auth::user()->type === Role::ROLE_INFLUENCER;
            $isSubscribed = true;
            $isFollowing  = true;

            $influencerId      = Auth::id();

            if (Auth::user()->type === Role::ROLE_FOLLOWER) {
                $follow            = Auth::user()->follows->first();
                $isFollowing       = $follow ? $follow->active_status : false;
                $influencerId      = $follow?->influencer_id;
                $feedEnabledPlanId = Plan::where('influencer_id', $influencerId)
                    ->where('is_feed_enabled', true)->pluck('id')->toArray();
                $isSubscribed = in_array(Auth::user()->plan_id, $feedEnabledPlanId);
            }
            $posts = Post::where('status', 'active');
            switch (request()->query('filter')) {
                case ('free'):
                    $posts = $posts->where('paid', false);
                    break;
                case ('paid'):
                    $posts = $posts->where('paid', true);
                    break;
                case ('influencer'):
                    $posts = $posts->where('isFollowerPost', false);
            }
            $posts = $posts->where('influencer_id', $influencerId)->orderBy('column_order', 'asc')->paginate(6);
            $posts->load('influencer');
            $posts->load('follower');
            $posts->load('purchasePost');
            return view('admin.posts.infinite', compact('posts', 'isInfluencer', 'isSubscribed', 'isFollowing'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function managePosts_old(PostDataTable $dataTable)
    {
        if (Auth::user()->can('manage-blog')) {
            return $dataTable->render('admin.posts.index');
        }
    }

    public function managePosts(Request $request)
    {
        if (!Auth::user()->can('manage-blog')) {
            abort(403, 'Unauthorized action.');
        }

        $user = Auth::user();
        $paymentFilter = $request->input('payment_filter'); // Get filter value
         $albumCategoryFilter = $request->input('album_category_filter'); // Get album category filter

        $query = Post::with('albumCategory') // Eager load albumCategory relationship
            ->select('post.*');

        if ($user->type == Role::ROLE_ADMIN) {
            // Admin sees all posts
        } elseif ($user->type == Role::ROLE_INFLUENCER) {
            $query->where('influencer_id', $user->id);
        } else {
            $query->where('follower_id', $user->id);
        }

        // Apply payment filter
        if ($paymentFilter) {
            if ($paymentFilter == 'paid') {
                // Posts that are paid OR albums with payment_mode = 'paid'
                $query->where(function ($q) {
                    $q->where('paid', true)
                        ->orWhereHas('albumCategory', function ($subQ) {
                            $subQ->where('payment_mode', 'paid');
                        });
                });
            } elseif ($paymentFilter == 'free') {
                // Posts that are free AND albums with payment_mode != 'paid'
                $query->where(function ($q) {
                    $q->where(function ($subQ) {
                        // Regular posts that are free
                        $subQ->where('paid', false)
                            ->whereNull('album_category_id');
                    })
                        ->orWhere(function ($subQ) {
                            // Albums that are not paid
                            $subQ->whereNotNull('album_category_id')
                                ->whereHas('albumCategory', function ($albumQ) {
                                    $albumQ->where('payment_mode', '!=', 'paid')
                                        ->orWhereNull('payment_mode');
                                });
                        });
                });
            }
        }

        if ($albumCategoryFilter) {
            $query->where('album_category_id', $albumCategoryFilter);
        }

        if ($request->ajax()) {
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('title', function ($post) {
                    return $post->title;
                })
                ->addColumn('paid', function ($post) {
                    // Check if album_category_id exists
                    if ($post->album_category_id) {
                        // Check album's payment_mode
                        return '-';
                        $isPaid = $post->albumCategory && $post->albumCategory->payment_mode == 'paid';
                        return $isPaid ? "Yes" : "No";
                    } else {
                        // Regular post - check paid field

                        return $post->paid == true ? "Yes" : "No";
                    }
                })
                ->addColumn('sales', function (Post $post) {
                    if ($post->album_category_id) {
                        // For albums, you might want to count album purchases
                        // Adjust this based on your actual relationship
                        return '-'; // Or implement album sales count
                    } else {
                        // Regular post sales
                        return PurchasePost::where('active_status', true)
                            ->where('post_id', $post->id)
                            ->count();
                    }
                })
                ->addColumn('price', function (Post $post) {
                    if ($post->album_category_id) {
                        return '-';
                        // Check album price
                        if ($post->albumCategory && $post->albumCategory->payment_mode == 'paid') {
                            return $post->albumCategory->price ?? 0;
                        }
                        return 0;
                    } else {
                        // Regular post price
                        return $post->paid == true ? $post->price : 0;
                    }
                })
                ->addColumn("photo", function (Post $post) {
                    if ($post->file) {
                        $imageSrc = $post->file;
                        if ($post->file_type == 'image') {
                            $return =  "<img src=' " . $imageSrc . " ' width='50'/>";
                        } else {
                            $thumbnail = asset('assets/images/video-thumbanail.jpeg');
                            $return = "<video width='50' class='video-thumbnail' data-video='" . $post->file . "' style='cursor:pointer;'>
                                    <source src='" . $post->file . "' type='video/mp4'>
                                    <img src='" . $thumbnail . "' width='50' />
                                  </video>";
                        }
                    } else {
                        $return = "<img src='" . asset('assets/images/image-thumbanail.jpeg') . "' width='50' />";
                    }
                    return $return;
                })
                ->addColumn('created_at', function ($request) {
                    $created_at = UtilityFacades::date_time_format($request->created_at);
                    return $created_at;
                })
                ->addColumn('action', function (Post $post) {
                    return view('admin.posts.action', compact('post'));
                })
                ->rawColumns(['action',  'photo'])
                ->make(true);
        }
        $albumCategories = AlbumCategory::query();

        if (Auth::user()->type == 'Influencer') {
            $albumCategories->where('instructor_id', Auth::user()->id);
        }
        $albumCategories = $albumCategories->get();
        return view('admin.posts.index', compact('albumCategories'));
    }

    public function manageReportedPosts(ReportedPostDataTable $dataTable)
    {
        if (Auth::user()->can('manage-blog')) {
            return $dataTable->render('admin.posts.index');
        }
    }

    public function create()
    {
        $settingData = UtilityFacades::getsettings('plan_setting');
        $plans       = json_decode($settingData, true);

        if (Auth::user()->can('create-blog')) {
            // $category = Category::where('status', 1)->pluck('name', 'id');
            $categories   = AlbumCategory::pluck('title', 'id');

            return view('admin.posts.create', compact('categories'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        // dd($_POST['description']);
        if (Auth::user()->can('create-blog')) {
            try {
                // if ($request->filled('category_id')) {
                //     // Prepare a new request for AlbumController
                //     $albumRequest = $request->merge([
                //         'album_category_id' => $request->input('category_id')
                //     ]);

                //     // Call AlbumController's store method
                //     return app(\App\Http\Controllers\Admin\AlbumController::class)->store($albumRequest);
                // }

                request()->validate([
                    'title'       => 'required|string',
                    // 'short_description' => 'required|string',
                    'price'       => ['nullable', 'numeric', 'gt:0.5'],
                    'filePath' => 'required',
                    'fileType' => 'required'
                ]);

                if (Auth::user()->type === Role::ROLE_FOLLOWER) {
                    $request->merge(['follower_id' => Auth::user()->id]);
                    $request->merge(['isFollowerPost' => true]);
                } else {
                    $request->merge(['influencer_id' => Auth::user()->id]);
                    $request->merge(['isFollowerPost' => false]);
                }
                $currentDomain  = tenant('domains');
                $currentDomain  = $currentDomain[0]->domain;
                $post           = Post::create($request->all());
                // dd("stop");
                if (!empty($request->category_id)) {
                    // $request->slug = Str::slug($request->title);
                    $paid = 0;
                    $price = 0;
                } else {
                    $paid =  $request?->paid == 1 ? true : false;
                    $price = $request?->paid == 1 ? $request?->price : 0;
                }

                // dd($paid,$price);

                $post['paid']   = $paid;
                $post['price']  = $price;
                $post['status'] = 'active';
                $post->file = $request->filePath; // Temporary chunk path
                $post->file_type = $request->fileType;
                $post->album_category_id = $request->category_id;
                $post->description = $_POST['description'];
                $post->short_description = $_POST['short_description'];

                if ($request->hasFile('thumbnail')) {
                    $tenantId = tenant('id');
                    $thumbnailFile = $request->file('thumbnail');
                    $thumbnailName = $tenantId . '/thumbnails/' . uniqid() . '_' . time() . '.' . $thumbnailFile->getClientOriginalExtension();
                    Storage::disk('spaces')->put($thumbnailName, file_get_contents($thumbnailFile), 'public');
                    $post->thumbnail = Storage::disk('spaces')->url($thumbnailName);
                }

                if ($request->hasFile('locked_thumbnail')) {
                    $tenantId = tenant('id');
                    $lockedFile = $request->file('locked_thumbnail');
                    $lockedName = $tenantId . '/locked-thumbnails/' . uniqid() . '_' . time() . '.' . $lockedFile->getClientOriginalExtension();
                    Storage::disk('spaces')->put($lockedName, file_get_contents($lockedFile), 'public');
                    $post->locked_thumbnail = Storage::disk('spaces')->url($lockedName);
                }

                // if ($request->hasFile('photo')) {
                //     $fileName = $request->file('photo');
                //     $filePath = $currentDomain . '/' . Auth::user()->id . '/posts' . $fileName;
                //     Storage::disk('spaces')->put($filePath, file_get_contents($fileName), 'public');
                //     $post->file      = Storage::disk('spaces')->url($filePath);
                //     $post->file_type = Str::contains($request->file('photo')->getMimeType(), 'video') ? 'video' : 'image';
                // }
                $post->save();
                return redirect()->route('blogs.index')->with('success', __('Post created successfully.'));
            } catch (ValidationException $e) {
                return redirect()->back()->withErrors($e->errors())->withInput();
            } catch (\Exception $e) {
                return response()->json(['error' => 'Error', 'message' => $e->getMessage()], 500);
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function edit($id)
    {
        if (Auth::user()->can('edit-blog')) {
            $posts    = Post::find($id);
            $categories = AlbumCategory::all();
            return view('admin.posts.edit', compact('posts', 'categories'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->can('edit-blog')) {
            request()->validate([
                'title'       => 'required|max:50',
                // 'description' => 'required',
                'price'       => ['nullable', 'numeric'],
            ]);
            $post          = Post::find($id);
            $currentDomain = tenant('domains');
            $currentDomain = $currentDomain[0]->domain;
            // if ($request->hasFile('file')) {
            //     $fileName = $request->file('file');
            //     $filePath = $currentDomain . '/' . Auth::user()->id . '/posts' . $fileName;
            //     Storage::disk('spaces')->put($filePath, file_get_contents($fileName), 'public');
            //     $post->file      = Storage::disk('spaces')->url($filePath);
            //     $post->file_type = Str::contains($request->file('file')->getMimeType(), 'video') ? 'video' : 'image';
            // }
            if (!empty($request->category_id)) {
                // $request->slug = Str::slug($request->title);
                $paid = 0;
                $price = 0;
            } else {
                $paid =  $request?->paid == 1 ? true : false;
                $price = $request?->paid == 1 ? $request?->price : 0;
            }
            $post->title       = $request->title;
            // $post->slug        = $request->slug;
            $post->paid        = $paid;
            $post->price       = $price;
            // $post->description = $request->description;
            // $post->short_description    = $request->short_description;
            $post->description = $_POST['description'];
            $post->short_description = $_POST['short_description'];
            $post->file = $request->filePath; // Temporary chunk path
            $post->file_type = $request->fileType;
            $post->album_category_id = $request->category_id;

            if ($request->hasFile('thumbnail')) {
                $tenantId = tenant('id');
                $thumbnailFile = $request->file('thumbnail');
                $thumbnailName = $tenantId . '/thumbnails/' . uniqid() . '_' . time() . '.' . $thumbnailFile->getClientOriginalExtension();
                Storage::disk('spaces')->put($thumbnailName, file_get_contents($thumbnailFile), 'public');
                $post->thumbnail = Storage::disk('spaces')->url($thumbnailName);
            }

            if ($request->hasFile('locked_thumbnail')) {
                $tenantId = tenant('id');
                $lockedFile = $request->file('locked_thumbnail');
                $lockedName = $tenantId . '/locked-thumbnails/' . uniqid() . '_' . time() . '.' . $lockedFile->getClientOriginalExtension();
                Storage::disk('spaces')->put($lockedName, file_get_contents($lockedFile), 'public');
                $post->locked_thumbnail = Storage::disk('spaces')->url($lockedName);
            }

            $post->save();
            return redirect()->route('blogs.manage')->with('success', __('Posts updated successfully'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->can('delete-blog')) {
            $post = Post::find($id);
            $post->delete();
            return redirect()->route('blogs.manage')->with('success', __('Posts deleted successfully.'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function upload(Request $request)
    {
        if ($request->hasFile('upload')) {
            $originName = $request->file('upload')->getClientOriginalName();
            $fileName   = pathinfo($originName, PATHINFO_FILENAME);
            $extension  = $request->file('upload')->getClientOriginalExtension();
            $fileName   = $fileName . '_' . time() . '.' . $extension;
            $request->file('upload')->move(public_path('images'), $fileName);
            $CKEditorFuncNum = $request->input('CKEditorFuncNum');
            $url             = asset('images/' . $fileName);
            $msg             = 'Image uploaded successfully';
            $response        = "<script>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url', '$msg')</script>";
            @header('Content-type: text/html; charset=utf-8');
            echo $response;
        }
    }

    public function allPost()
    {
        $categories    = Category::all();
        $category      = [];
        $category['0'] = __('Select category');
        foreach ($categories as $cate) {
            $category[$cate->id] = $cate->name;
        }
        $posts = Post::all();
        return view('admin.posts.view', compact('posts', 'category'));
    }

    public function viewBlog($slug)
    {
        $lang = UtilityFacades::getActiveLanguage();
        \App::setLocale($lang);
        $blog     = Post::where('slug', $slug)->first();
        $allBlogs = Post::all();
        return view('admin.posts.view-blog', compact('blog', 'allBlogs', 'lang'));
    }

    public function seeAllBlogs(Request $request)
    {
        $lang = UtilityFacades::getActiveLanguage();
        \App::setLocale($lang);
        if ($request->category_id != '') {
            $allBlogs = Post::where('category_id', $request->category_id)->paginate(3);
            return response()->json(['all_blogs' => $allBlogs]);
        } else {
            $allBlogs = Post::paginate(3);
        }
        $recentBlogs = Post::latest()->take(3)->get();
        $lastBlog    = Post::latest()->first();
        $categories  = Category::all();
        return view('admin.posts.view-all-blogs', compact('allBlogs', 'recentBlogs', 'lastBlog', 'categories', 'lang'));
    }

    public function likePost()
    {
        try {
            $post = Post::find(request()->post_id);
            if (! ! $post) {
                $postLike = Auth::user()->likePost->firstWhere('post_id', $post->id);

                if (! ! $postLike) {
                    $postLike->delete();
                    return redirect()->back()->with('success', __('Unliked'));
                }

                $postLike          = new LikePost();
                $postLike->post_id = $post->id;
                if (Auth::user()->type === Role::ROLE_FOLLOWER) {
                    $postLike->follower_id = Auth::user()->id;
                } else {
                    $postLike->influencer_id = Auth::user()->id;
                }

                $postLike->save();
                return redirect()->back()->with('success', __('Post Liked Successfully'));
            } else {
                return redirect()->back()->with('failed', __('UnSuccessfull'));
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    //APP APIs
    public function updatePostApi($id)
    {
        try {
            $post = Post::find($id);
            $user = Auth::user();
            if (! ! $post && $user->post->firstWhere('id', $post->id)) {
                request()->validate([
                    'title'       => 'string|max:255',
                    'description' => 'string|max:255',
                    'paid'        => 'boolean',
                    'price'       => 'string|max:6',
                    'status'      => 'in:active,inactive',
                ]);
                if ($post->isFollowerPost) {
                    $post->update(request()->only('title', 'description', 'short_description', 'status'));
                } else {
                    $post->update(request()->all());
                }

                $post->save();
                return response()->json(new PostAPIResource($post), 200);
            } else {
                return response()->json(['error' => 'Post does not exist for logged in user'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function likePostAPi($id)
    {
        try {
            $post = Post::find($id);

            if (! ! $post) {
                $postLike = Auth::user()->likePost->firstWhere('post_id', $post->id);

                if (! ! $postLike) {
                    $postLike->delete();
                    return response()->json(['message' => 'unliked successfully'], 200);
                }

                $postLike          = new LikePost();
                $postLike->post_id = $post->id;
                if (Auth::user()->type === Role::ROLE_FOLLOWER) {
                    $postLike->follower_id = Auth::user()->id;
                } else {
                    $postLike->influencer_id = Auth::user()->id;
                }

                $postLike->save();
                return response()->json(new PostAPIResource($post), 200);
            } else {
                return response()->json(['error' => 'Post does not exist'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function reportPost(Request $request)
    {

        try {
            $request->validate([
                'post_id' => 'required',
                'comment' => 'max:255',
            ]);
            $post = Post::find($request->get('post_id'));
            if (! ! $post) {
                $reportPost          = new ReportPost();
                $reportPost->post_id = $post->id;

                if (Auth::user()->type === Role::ROLE_FOLLOWER) {
                    $reportPost->follower_id = Auth::user()->id;
                } else {
                    $reportPost->influencer_id = Auth::user()->id;
                }

                if (isset($request->comment)) {
                    $reportPost->comment = $request->comment;
                }

                $reportPost->save();

                return response()->json(['message' => 'post reported successfully'], 200);
            } else {
                return response()->json(['error' => 'Post does not exist'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getAllLikedPostApi()
    {
        try {

            $likedPosts = new Collection();
            $posts      = Auth::user()->likePost;

            foreach ($posts as $item) {
                $post = $item->post;
                $likedPosts->push($post);
            }

            return PostAPIResource::collection($likedPosts);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getAllPosts()
    {
        try {
            if (Auth::user()->can('manage-blog')) {
                $posts = Post::where('status', 'active')->orderBy(request()->get('sortKey', 'updated_at'), request()->get('sortOrder', 'desc'));
                switch (request()->filter) {
                    case ('free'):
                        $posts = $posts->where('paid', false);
                        break;
                    case ('paid'):
                        $posts = $posts->where('paid', true);
                        break;
                    case ('follower'):
                        $posts = $posts->where('isFollowerPost', true);
                        break;
                    case ('influencer'):
                        $posts = $posts->where('isFollowerPost', false);
                        break;
                    case ('myPosts'):
                        if (Auth::user()->type === Role::ROLE_INFLUENCER) {
                            $posts = $posts->where('influencer_id', Auth::user()->id);
                        } else {
                            $posts = $posts->where('follower_id', Auth::user()->id);
                        }
                        break;
                    case ('subscribed'):
                        if (Auth::user()->type === Role::ROLE_FOLLOWER) {
                            $ids   = Auth::user()->follows->where('active_status', true)->where('isPaid', true)->implode('influencer_id', ',');
                            $posts = $posts->whereIn('influencer_id', explode(',', $ids));
                        } else {
                            throw new UnauthorizedException('This filter is only valid for followers', 404);
                        }
                        break;
                    case ('followed'):
                        if (Auth::user()->type === Role::ROLE_FOLLOWER) {
                            $ids   = Auth::user()->follows->where('active_status', true)->implode('influencer_id', ',');
                            $posts = $posts->whereIn('influencer_id', explode(',', $ids));
                        } else {
                            throw new UnauthorizedException('This filter is only valid for followers', 404);
                        }
                        break;
                }
                return PostAPIResource::collection($posts->paginate(request()->get('perPage', 10)));
            } else {
                return response()->json(['error' => 'Access denied'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching posts.', 'message' => $e->getMessage()], 500);
        }
    }

    public function getInfluencerPosts(Request $request)
    {
        try {
            $this->validate($request, [
                'influencer_id' => 'required',
            ]);

            if (Auth::user()->can('manage-blog')) {
                $posts = PostAPIResource::collection(Post::with('influencer')
                    ->where('influencer_id', $request?->influencer_id)
                    ->orderBy(request()->get('sortKey', 'updated_at'), request()->get('sortOrder', 'desc'))
                    ->paginate(request()->get('per_page', 10)));
                return $posts;
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error fetching posts.', 'message' => $e->getMessage()], 500);
        }
    }

    public function createPost(Request $request)
    {
        try {
            request()->validate([
                'title'       => 'required|string|max:255',
                'description' => 'required|string|max:255',
                'paid'        => 'boolean',
                'price'       => 'string|max:6',
                'status'      => 'in:active,inactive',
            ]);

            if (Auth::user()->type === Role::ROLE_FOLLOWER) {
                $request->merge(['follower_id' => Auth::user()->id]);
                $request->merge(['isFollowerPost' => true]);
            } else {
                $request->merge(['influencer_id' => Auth::user()->id]);
                $request->merge(['isFollowerPost' => false]);
            }

            $post = Post::create($request->all());

            $post['paid']  = $request?->paid ?? false;
            $post['price'] = $request?->paid ? $request?->price : 0;

            if ($request->hasfile('file')) {
                $post['file']      = $request->file('file')->store('posts');
                $post['file_type'] = Str::contains($request->file('file')->getMimeType(), 'video') ? 'video' : 'image';
            }

            $post->update();

            return response()->json(new PostAPIResource($post), 200);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error creating post.', 'message' => $e->getMessage()], 500);
        }
    }

    public function reorder(Request $request)
    {

        foreach ($request->order as $item) {
            Post::where('id', $item['id'])
                ->update(['column_order' => $item['position']]);
        }

        return response()->json(['success' => true]);
    }

    public function reorder1()
    {
        if (!Auth::user()->can('manage-blog')) {
            abort(403, 'Unauthorized action.');
        }

        $user = Auth::user();
        $query = Post::query();

        if ($user->type == Role::ROLE_ADMIN) {
            $posts = $query->orderBy('column_order', 'asc')->get();
        } elseif ($user->type == Role::ROLE_INFLUENCER) {
            $posts = $query->where('influencer_id', $user->id)->orderBy('column_order', 'asc')->get();
        } else {
            $posts = $query->where('follower_id', $user->id)->orderBy('column_order', 'asc')->get();
        }

        return view('admin.posts.reorder', compact('posts'));
    }

    public function updateOrder(Request $request)
    {
        if (!Auth::user()->can('manage-blog')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        try {
            $order = $request->input('order');

            if (empty($order)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No order data provided.'
                ]);
            }

            foreach ($order as $item) {
                Post::where('id', $item['id'])->update([
                    'column_order' => $item['position']
                ]);
            }

            // Get updated order for response (optional)
            $user = Auth::user();
            $query = Post::query();

            if ($user->type == Role::ROLE_ADMIN) {
                $posts = $query->orderBy('column_order', 'asc')->get();
            } elseif ($user->type == Role::ROLE_INFLUENCER) {
                $posts = $query->where('influencer_id', $user->id)->orderBy('column_order', 'asc')->get();
            } else {
                $posts = $query->where('follower_id', $user->id)->orderBy('column_order', 'asc')->get();
            }

            $newOrder = $posts->map(function ($post) {
                return [
                    'id' => $post->id,
                    'column_order' => $post->column_order
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully.',
                'new_order' => $newOrder
            ]);
        } catch (\Exception $e) {
            \Log::error('Reorder error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error updating order: ' . $e->getMessage()
            ], 500);
        }
    }
}
