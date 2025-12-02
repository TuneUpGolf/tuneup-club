<?php

namespace App\Http\Controllers\Admin;

use App\Models\Album;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\AlbumCategory;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\DataTables\Admin\AlbumDataTable;
use Illuminate\Validation\ValidationException;

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
                    'album_category_id' => 'required|exists:album_categories,id',
                     'filePath' => 'required',
                    'fileType' => 'required'
                ]);
                $album_category = new Album();
                $album_category->instructor_id = Auth::user()->id;
                $album_category->album_category_id = $request->input('album_category_id');
                $album_category->tenant_id = tenant('id');
                $album_category->title = $request->title;
                $album_category->slug = Str::slug($request->title);
                $album_category->description = $request->description;
                // $album_category->file_type = Str::contains($request->file('file')->getMimeType(), 'video') ? 'video' : 'image';

                   $album_category->media = $request->filePath; // Temporary chunk path
                $album_category->file_type = $request->fileType;
                // if ($request->hasfile('file')) {
                //     $tenantId = tenant()->id;
                //     $destination = public_path("{$tenantId}/album");
                //     if (!file_exists($destination)) {
                //         mkdir($destination, 0777, true);
                //     }
                //     $filename = time() . '_' . $request->file('file')->getClientOriginalName();
                //     $request->file('file')->move($destination, $filename);
                //     $album_category->media = "{$tenantId}/album/{$filename}";
                //     $mimeType = $request->file('file')->getClientOriginalExtension();
                //     $video_types = ['mp4', 'avi', 'mov', 'mkv', 'flv', 'wmv', 'webm', 'mpeg', '3gp'];
                //     $album_category->file_type = in_array($mimeType, $video_types) ? 'video' : 'image';
                //     // $album_category->file_type = Str::contains($mimeType, 'video') ? 'video' : 'image';
                //     // $file = $request->file('file')->store('album');
                //     // $album_category->media = $file ?? null;
                // }

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
            // if ($request->hasFile('file')) {
            //     $tenantId = tenant()->id; // e.g. 3
            //     $destination = public_path("{$tenantId}/album");
            //     if (!file_exists($destination)) {
            //         mkdir($destination, 0777, true);
            //     }
            //     $filename = time() . '_' . $request->file('file')->getClientOriginalName();
            //     $request->file('file')->move($destination, $filename);
            //     $album_category->media = "{$tenantId}/album/{$filename}";

            //     $mimeType = $request->file('file')->getClientOriginalExtension();
            //     $video_types = ['mp4', 'avi', 'mov', 'mkv', 'flv', 'wmv', 'webm', 'mpeg', '3gp'];
            //     $album_category->file_type = in_array($mimeType, $video_types) ? 'video' : 'image';
            //     // $album_category->file_type = Str::contains($mimeType, 'video') ? 'video' : 'image';
            //     // $path           = $request->file('file')->store('posts');
            //     // $album_category->media    = $path;
            // }
            $album_category->instructor_id = Auth::user()->id;
            $album_category->album_category_id = $request->input('album_category_id');
            $album_category->tenant_id = tenant('id');
            $album_category->title = $request->title;
            $album_category->slug = Str::slug($request->title);
            $album_category->description = $request->description;
             $album_category->media = $request->filePath; // Temporary chunk path
            $album_category->file_type = $request->fileType;
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

    public function uploadChunk(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file',
                'chunkIndex' => 'required|integer',
                'totalChunks' => 'required|integer',
                'originalName' => 'required|string',
                'uploadId' => 'required|string'
            ]);

            $file = $request->file('file');
            $chunkIndex = (int) $request->chunkIndex;
            $totalChunks = $request->totalChunks;
            $originalName = $request->originalName;
            $uploadId = $request->uploadId;
            $folderName = isset($request->folderName) ? $request->folderName : 'posts';
            $tenantId = tenant()->id;

            // Generate unique file name with tenant prefix
            $fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);
            $fileName = "{$tenantId}/{$folderName}/" . uniqid() . '_' . time() . '.' . $fileExtension;

            // Initialize multipart upload if this is the first chunk
            // dd(Storage::disk('spaces')->getClient());
            if ($chunkIndex === 0) {
                // $client = Storage::disk('spaces')->getDriver()->getAdapter()->getClient();
                $client = Storage::disk('spaces')->getClient();

                $multipartUpload = $client->createMultipartUpload([
                    'Bucket' => env('DO_SPACES_BUCKET'),
                    'Key'    => $fileName,
                    'ACL'    => 'public-read',
                ]);

                // Store uploadId and fileName in session or cache for later use
                session(["multipart_upload_{$uploadId}" => [
                    'uploadId' => $multipartUpload['UploadId'],
                    'fileName' => $fileName
                ]]);
            }

            // Get upload details from session
            $uploadDetails = session("multipart_upload_{$uploadId}");
            if (!$uploadDetails) {
                throw new \Exception('Upload session not found');
            }

            $storedUploadId = $uploadDetails['uploadId'];
            $storedFileName = $uploadDetails['fileName'];

            // Upload the chunk
            // $client = Storage::disk('spaces')->getDriver()->getAdapter()->getClient();
            $client = Storage::disk('spaces')->getClient();
            $uploadPartResult = $client->uploadPart([
                'Bucket'     => env('DO_SPACES_BUCKET'),
                'Key'        => $storedFileName,
                'UploadId'   => $storedUploadId,
                'PartNumber' => $chunkIndex + 1, // Part numbers must start from 1
                'Body'       => fopen($file->getPathname(), 'rb'),
            ]);

            // Store ETag for completion
            $parts = session("multipart_parts_{$uploadId}", []);
            $parts[] = [
                'ETag' => $uploadPartResult['ETag'],
                'PartNumber' => $chunkIndex + 1
            ];
            session(["multipart_parts_{$uploadId}" => $parts]);

            // Check if this is the last chunk
            $isComplete = ($chunkIndex === $totalChunks - 1);

            if ($isComplete) {
                // Complete multipart upload
                $client->completeMultipartUpload([
                    'Bucket' => env('DO_SPACES_BUCKET'),
                    'Key' => $storedFileName,
                    'UploadId' => $storedUploadId,
                    'MultipartUpload' => [
                        'Parts' => session("multipart_parts_{$uploadId}")
                    ],
                ]);

                // Clean up session
                session()->forget("multipart_upload_{$uploadId}");
                session()->forget("multipart_parts_{$uploadId}");

                // Determine file type
                $extension = strtolower($fileExtension);

                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
                $videoExtensions = ['mp4', 'mov', 'avi', 'mkv', 'wmv', 'webm'];

                if (in_array($extension, $imageExtensions)) {
                    $fileType = 'image';
                } elseif (in_array($extension, $videoExtensions)) {
                    $fileType = 'video';
                } else {
                    $fileType = 'file';
                }

                return response()->json([
                    'success' => true,
                    'chunkIndex' => $chunkIndex,
                    'completed' => true,
                    'fileUrl' => Storage::disk('spaces')->url($storedFileName),
                    'fileName' => $storedFileName,
                    'fileType' => $fileType // <-- add this
                ]);
            }

            return response()->json([
                'success' => true,
                'chunkIndex' => $chunkIndex,
                'completed' => false
            ]);
        } catch (\Exception $e) {
            \Log::error('Chunk upload failed: ' . $e->getMessage());

            // Clean up session on error
            $uploadId = $request->uploadId ?? '';
            if ($uploadId) {
                session()->forget("multipart_upload_{$uploadId}");
                session()->forget("multipart_parts_{$uploadId}");
            }

            return response()->json([
                'success' => false,
                'message' => 'Chunk upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function finalizeUpload(Request $request)
    {
        try {
            $request->validate([
                'fileName' => 'required|string',
                'uploadId' => 'required|string'
            ]);

            $fileName = $request->fileName;

            // Generate the public URL for the uploaded file
            $fileUrl = Storage::disk('spaces')->url($fileName);

            // Optional: Verify the file exists in Spaces
            if (!Storage::disk('spaces')->exists($fileName)) {
                throw new \Exception('File not found in storage');
            }

            // Optional: Get file size and other metadata
            $fileSize = Storage::disk('spaces')->size($fileName);
            $mimeType = Storage::disk('spaces')->mimeType($fileName);

            return response()->json([
                'success' => true,
                'fileUrl' => $fileUrl,
                'fileName' => $fileName,
                'fileSize' => $fileSize,
                'mimeType' => $mimeType,
                'message' => 'File uploaded successfully and is now available'
            ]);
        } catch (\Exception $e) {
            \Log::error('Finalize upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Finalization failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
