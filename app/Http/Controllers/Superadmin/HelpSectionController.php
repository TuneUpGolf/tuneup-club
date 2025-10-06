<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Facades\UtilityFacades;
use App\Models\HelpSection;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class HelpSectionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user ? $user->roles->pluck('name')->first() : null;

        $help_sections = HelpSection::when($user, function ($query) use ($user) {
            if ($user->hasRole('influencer')) {
                return $query->where('role', 'influencer');
            } elseif ($user->hasRole('Follower')) {
                return $query->where('role', 'follower');
            }
            return $query;
        })
            ->orderBy('id', 'desc')
            ->paginate(8);
        return view('superadmin.help-section.index', compact('help_sections', 'role'));
    }

    public function create()
    {
        $user = Auth::user();
        $current_role = $user ? $user->roles->pluck('name')->first() : null;
        if ($current_role != 'Admin' && $current_role != 'Super Admin') {
            return redirect()->route('help-section.index')->with('error', "You do not have permission to create help section");
        }
        $databasePermission = UtilityFacades::getsettings('database_permission');
        $roles = array(
            "influencer" => "Influencer",
            "follower" => "Follower"
        );
        return view('superadmin.help-section.create', compact('databasePermission', 'roles'));
    }

    public function store(Request $request)
    {


        request()->validate([
            'role' =>   'required|string|in:influencer,follower',
            'title' => 'required',
            'type' => 'required',
            'uploadFileName' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $help_section = new HelpSection();
            $help_section->role = $request->role;
            $help_section->title = $request->title;
            $help_section->url = $request->uploadFileName;
            $help_section->type = $request->type;
            $help_section->save();
            DB::commit();
            return redirect()->route('help-section.index')->with('success', 'Help Section created successfully.');
        } catch (Exception $ex) {
            DB::rollBack();
            return redirect()->back()->with('failed', $ex->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {

        $helpSection = HelpSection::findOrFail($id);
        // Verify user role
        if (!Auth::user()->hasAnyRole(['Admin', 'Super Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $filePath = "{$helpSection->url}"; // e.g., videos/Learn MongoDB in 1 Hour 🍃.mp4

        // Adjust path for the public disk (relative to storage/app/public)
        $publicDiskPath = $filePath; // Assuming videos/ is under storage/app/public
        $file = strstr($publicDiskPath, 'videos/');
        $file = str_replace('videos/', '', $file);

        if (!is_null($file)) {
            if (Storage::disk('videos')->exists($file)) {
                Storage::disk('videos')->delete($file);
            }
        }

        // Check if file exists and delete

        // // Delete database record
        $helpSection->delete();

        // $helpSection = HelpSection::findOrFail($id);
        // // Verify user role
        // if (!Auth::user()->hasAnyRole(['Admin', 'Super Admin'])) {
        //     return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        // }

        // $filePath = "assets/videos/{$helpSection->url}";
        // $publicPath = public_path($filePath);

        // // Check if file exists and delete
        // if (File::exists($publicPath)) {
        //     File::delete($publicPath);
        // }

        // // Delete database record
        // $helpSection->delete();

        return response()->json(['success' => true, 'message' => 'Item deleted successfully']);
    }
}
