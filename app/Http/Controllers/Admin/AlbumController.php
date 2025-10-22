<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\AlbumDataTable;
use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\AlbumCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AlbumController extends Controller
{
    public function index(AlbumDataTable $dataTable)
    {
        if (Auth::user()->can('manage-blog')) {
            return $dataTable->render('admin.album.index');
        }
    }
    public function create()
    {
        if (Auth::user()->can('create-blog')) {
            $album_categories = AlbumCategory::where('instructor_id', Auth::user()->id)->get(['id', 'title']);
            return  view('admin.album.create', compact('album_categories'));
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
                    'album_category_id' => 'required|exists:album_categories,id'
                ]);
                $album_category = new Album();
                $album_category->instructor_id = Auth::user()->id;
                $album_category->album_category_id = $request->input('album_category_id');
                $album_category->tenant_id = tenant('id');
                $album_category->title = $request->title;
                $album_category->slug = Str::slug($request->title);
                $album_category->description = $request->description;
                // $album_category->file_type = Str::contains($request->file('file')->getMimeType(), 'video') ? 'video' : 'image';

                if ($request->hasfile('file')) {
                    $tenantId = tenant()->id;
                    $destination = public_path("{$tenantId}/album");
                    if (!file_exists($destination)) {
                        mkdir($destination, 0777, true);
                    }
                    $filename = time() . '_' . $request->file('file')->getClientOriginalName();
                    $request->file('file')->move($destination, $filename);
                    $album_category->media = "{$tenantId}/album/{$filename}";
                    $mimeType = $request->file('file')->getClientOriginalExtension();
                    $video_types = ['mp4', 'avi', 'mov', 'mkv', 'flv', 'wmv', 'webm', 'mpeg', '3gp'];
                    $album_category->file_type = in_array($mimeType, $video_types) ? 'video' : 'image';
                    // $album_category->file_type = Str::contains($mimeType, 'video') ? 'video' : 'image';
                    // $file = $request->file('file')->store('album');
                    // $album_category->media = $file ?? null;
                }

                $album_category->status = 'active';
                $album_category->save();
                return redirect()->route('album.category.manage')->with('success', __('Album created successfully.'));
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
            $post = Album::find($id);
            $post->delete();
            return redirect()->route('album.category.album', ['id' => $post->album_category_id])->with('success', __('Album deleted successfully.'));
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
                'album_category_id' => 'required|exists:album_categories,id'
            ]);
            $album_category   = Album::find($id);
            if ($request->hasFile('file')) {
                $tenantId = tenant()->id; // e.g. 3
                $destination = public_path("{$tenantId}/album");
                if (!file_exists($destination)) {
                    mkdir($destination, 0777, true);
                }
                $filename = time() . '_' . $request->file('file')->getClientOriginalName();
                $request->file('file')->move($destination, $filename);
                $album_category->media = "{$tenantId}/album/{$filename}";

                $mimeType = $request->file('file')->getClientOriginalExtension();
                $video_types = ['mp4', 'avi', 'mov', 'mkv', 'flv', 'wmv', 'webm', 'mpeg', '3gp'];
                $album_category->file_type = in_array($mimeType, $video_types) ? 'video' : 'image';
                // $album_category->file_type = Str::contains($mimeType, 'video') ? 'video' : 'image';
                // $path           = $request->file('file')->store('posts');
                // $album_category->media    = $path;
            }
            $album_category->instructor_id = Auth::user()->id;
            $album_category->album_category_id = $request->input('album_category_id');
            $album_category->tenant_id = tenant('id');
            $album_category->title = $request->title;
            $album_category->slug = Str::slug($request->title);
            $album_category->description = $request->description;
            // $album_category->file_type = array_key_exists('file',$request->all()) ? (Str::contains($request->file('file')->getMimeType(), 'video') ? 'video' : 'image') : $album_category->file_type;
            $album_category->save();
            return redirect()->route('album.category.album', ['id' => $album_category->album_category_id])->with('success', __('Album updated successfully'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function edit($id)
    {
        if (Auth::user()->can('edit-blog')) {
            $posts      = Album::find($id);
            if (!is_null($posts)) {
                $album_categories = AlbumCategory::where('instructor_id', Auth::user()->id)->get(['id', 'title']);
                return  view('admin.album.edit', compact('posts', 'album_categories'));
            } else {
                return redirect()->back()->with('failed', __('Album not found.'));
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }
}
