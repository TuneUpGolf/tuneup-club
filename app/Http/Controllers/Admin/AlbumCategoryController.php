<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\AlbumCategoryDataTable;
use App\Http\Controllers\Controller;
use App\Models\AlbumCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AlbumCategoryController extends Controller
{

    public function index(AlbumCategoryDataTable $dataTable)
    {
        if (Auth::user()->can('manage-blog')) {
            return $dataTable->render('admin.album.category.index');
        }
    }

    public function create()
    {
        if (Auth::user()->can('create-blog')) {
            return  view('admin.album.category.create');
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }
    public function store(Request $request)
    {
        if (Auth::user()->can('create-blog')) {
            try {
                request()->validate([
                    'title' => 'required|string',
                    'description' => 'required|string',
                ]);
                $album_category = new AlbumCategory();
                $album_category->instructor_id = Auth::user()->id;
                $album_category->tenant_id = tenant('id');
                $album_category->title = $request->title;
                $album_category->slug = Str::slug($request->title);
                $album_category->description = $request->description;
                $album_category->payment_mode = array_key_exists('paid', $request->all()) ? ($request?->paid == 'on' ? "paid" : "un-paid") : "un-paid";
                $album_category->price =  array_key_exists('paid', $request->all()) ? ($request?->paid == 'on' && !empty($request?->price) ? $request?->price : 0) : 0;
                // $album_category->file_type = Str::contains($request->file('file')->getMimeType(), 'video') ? 'video' : 'image';

                if ($request->hasfile('file')) {
                    // $file = $request->file('file')->store('album_category');
                    // $album_category->image = $file ?? null;
                    $tenantId = tenant()->id; // e.g. 3
                    $destination = public_path("{$tenantId}/album_category");
                    if (!file_exists($destination)) {
                        mkdir($destination, 0777, true);
                    }
                    $filename = time() . '_' . $request->file('file')->getClientOriginalName();
                    $request->file('file')->move($destination, $filename);
                    $album_category->image = "{$tenantId}/album_category/{$filename}";

                    $mimeType = $request->file('file')->getClientOriginalExtension();
                    $video_types = ['mp4', 'avi', 'mov', 'mkv', 'flv', 'wmv', 'webm', 'mpeg', '3gp'];
                    $album_category->file_type = in_array($mimeType, $video_types) ? 'video' : 'image';
                }
                $album_category->status = 'active';
                $album_category->save();
                return redirect()->route('album.category.manage')->with('success', __('Album Category created successfully.'));
            } catch (ValidationException $e) {
                Log::info($e->getMessage());
                return redirect()->back()->withErrors($e->errors())->withInput();
            } catch (\Exception $e) {
                Log::info($e->getMessage());
                return redirect()->back()->with('danger', $e->getMessage())->withInput();
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->can('delete-blog')) {
            $post = AlbumCategory::find($id);
            $post->delete();
            return redirect()->route('album.category.manage')->with('success', __('Album Category deleted successfully.'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->can('edit-blog')) {
            request()->validate([
                'title'         => 'required|max:50',
                'description'   => 'required',
            ]);
            $album_category   = AlbumCategory::find($id);
            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                // $path           = $request->file('file')->store('album_category');
                // $album_category->image    = $path;
                $tenantId = tenant()->id; // e.g. 3
                $destination = public_path("{$tenantId}/album_category");
                if (!file_exists($destination)) {
                    mkdir($destination, 0777, true);
                }
                $filename = time() . '_' . $request->file('file')->getClientOriginalName();
                $request->file('file')->move($destination, $filename);
                $album_category->image = "{$tenantId}/album_category/{$filename}";
                $mimeType = $request->file('file')->getClientOriginalExtension();
                $video_types = ['mp4', 'avi', 'mov', 'mkv', 'flv', 'wmv', 'webm', 'mpeg', '3gp'];
                $album_category->file_type = in_array($mimeType, $video_types) ? 'video' : 'image';
            }
            $album_category->instructor_id = Auth::user()->id;
            $album_category->tenant_id = tenant('id');
            $album_category->title = $request->title;
            $album_category->slug = Str::slug($request->title);
            $album_category->description = $request->description;
            $album_category->payment_mode = array_key_exists('paid', $request->all()) ? ($request?->paid == 'on' ? 'paid' : 'un-paid') : 'un-paid';
            $album_category->price = array_key_exists('paid', $request->all()) && $request?->paid == 'on' && !empty($request?->price) ? $request?->price : 0;
            $album_category->save();
            return redirect()->route('album.category.manage')->with('success', __('Album Category updated successfully'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function edit($id)
    {
        if (Auth::user()->can('edit-blog')) {
            $posts      = AlbumCategory::find($id);
            if (!is_null($posts)) {
                return  view('admin.album.category.edit', compact('posts'));
            } else {
                return redirect()->back()->with('failed', __('Album Category not found.'));
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }
}
