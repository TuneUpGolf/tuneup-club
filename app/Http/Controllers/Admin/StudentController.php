<?php
namespace App\Http\Controllers\Admin;

use App\Actions\SendEmail;
use App\Actions\SendSMS;
use App\DataTables\Admin\StudentDataTable;
use App\Facades\UtilityFacades;
use App\Http\Controllers\Controller;
use App\Http\Resources\StudentAPIResource;
use App\Imports\StudentsImport;
use App\Mail\Admin\WelcomeMail;
use App\Mail\Admin\WelcomeMailStudent;
use App\Models\Role;
use App\Models\Student;
use Carbon\Carbon;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Stancl\Tenancy\Database\Models\Domain;

class StudentController extends Controller
{

    public function index(StudentDataTable $dataTable)
    {

        if (Auth::user()->can('manage-students')) {
            return $dataTable->render('admin.students.index');
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (Auth::user()->can('create-students')) {
            return view('admin.students.create');
        }
        return redirect()->back()->with('failed', __('Permission denied.'));
    }

    public function import()
    {

        if (Auth::user()->can('create-students')) {
            return view('admin.students.import');
        }
        return redirect()->back()->with('failed', __('Permission denied.'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('create-students')) {
            request()->validate([
                'name'         => 'required|max:50',
                'email'        => 'required|email|unique:students,email|unique:users,email',
                'country_code' => 'required',
                'dial_code'    => 'required',
                'phone'        => 'required',
            ]);
            $randomPassword                = Str::random(10);
            $userData                      = $request->all();
            $userData['uuid']              = Str::uuid();
            $userData['password']          = Hash::make($randomPassword);
            $userData['type']              = 'Student';
            $userData['created_by']        = Auth::user()->id;
            $userData['email_verified_at'] = (UtilityFacades::getsettings('email_verification') == '1') ? null : Carbon::now()->toDateTimeString();
            $userData['phone_verified_at'] = (UtilityFacades::getsettings('phone_verification') == '1') ? null : Carbon::now()->toDateTimeString();
            $userData['country_code']      = $request->country_code;
            $userData['dial_code']         = $request->dial_code;
            $userData['phone']             = str_replace(' ', '', $request->phone);
            $user                          = Student::create($userData);
            $user->assignRole(Role::ROLE_STUDENT);
            if ($request->hasFile('dp')) {
                $user['dp'] = $request->file('dp')->store('dp');
            }
            $user->update();
            SendEmail::dispatch($userData['email'], new WelcomeMailStudent($user, $randomPassword));
            $message = __('Welcome, :name, you have successfully signed up!, Please login with password :password at :link', [
                'name'     => $userData['name'],
                'password' => $randomPassword,
                'link'     => route('login'),
            ]);
            // $userPhone = Str::of($userData['dial_code'])->append($userData['phone'])->value();
            // $userPhone = str_replace(array('(', ')'), '', $userPhone);
            // SendSMS::dispatch("+" . $userPhone, $message);

            return redirect()->route('student.index')->with('success', __('Student created successfully.'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function edit($id)
    {
        if (Auth::user()->can('edit-user')) {
            $user = Student::find($id);
            if (Auth::user()->type == 'Admin') {
                $roles   = Role::where('name', '!=', 'Super Admin')->where('name', '!=', 'Admin')->pluck('name', 'name');
                $domains = Domain::pluck('domain', 'domain')->all();
            } else {
                $roles   = Role::where('name', '!=', 'Admin')->where('name', Auth::user()->type)->pluck('name', 'name');
                $domains = Domain::pluck('domain', 'domain')->all();
            }
            return view('admin.students.edit', compact('user', 'roles', 'domains'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->can('edit-user')) {
            request()->validate([
                'name'         => 'required|max:50',
                'country_code' => 'required',
                'dial_code'    => 'required',
                'phone'        => 'required',
                'password'     => 'same:password_confirmation',

            ]);
            $input              = $request->all();
            $user               = Student::find($id);
            $user->country_code = $request->country_code;
            $user->dial_code    = $request->dial_code;
            $user->phone        = str_replace(' ', '', $request->phone);
            $user->update($input);
            if (! empty($request->password)) {
                $user->password = bcrypt($request->password);
                $user->save();
            }

            return redirect()->route('student.index')->with('success', __('User updated successfully.'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->can('delete-user')) {
            $user = Student::find($id);
            $user->purchasePost()->delete();
            $user->purchase()->delete();
            $user->follows()->delete();
            $user->post()->delete();
            $user->delete();

            return redirect()->route('student.index')->with('success', __('User deleted successfully.'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function deleteAPI($id)
    {
        try {
            if (Auth::user()->id == $id && Auth::user()->type === Role::ROLE_STUDENT) {
                $user = Student::find($id);
                $user->purchasePost()->delete();
                $user->purchase()->delete();
                $user->follows()->delete();
                $user->post()->delete();
                $user->delete();
                return response()->json(['message' => 'Student successfully deleted '], 200);
            } else {
                response()->json(['message' => 'unsuccessful'], 419);
            }
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }

    public function signup(Request $request)
    {

        try {

            request()->validate([
                'name'         => 'required|max:50',
                'email'        => 'required|email|unique:students,email|unique:users,email',
                'password'     => 'same:confirm-password',
                'country_code' => 'required',
                'dial_code'    => 'required',
                'phone'        => 'required',
                'bio'          => 'nullable|max:250',
            ]);

            $userData                      = $request->all();
            $userData['password']          = Hash::make($userData['password']);
            $userData['type']              = Role::ROLE_STUDENT;
            $userData['created_by']        = "signup";
            $userData['email_verified_at'] = (UtilityFacades::getsettings('email_verification') == '1') ? null : Carbon::now()->toDateTimeString();
            $userData['phone_verified_at'] = (UtilityFacades::getsettings('phone_verification') == '1') ? null : Carbon::now()->toDateTimeString();
            $userData['country_code']      = $request?->country_code;
            $userData['dial_code']         = $request?->dial_code;
            $userData['bio']               = $request?->bio;
            $userData['phone']             = str_replace(' ', '', $request->phone);
            $user                          = Student::create($userData);
            $user->assignRole(Role::ROLE_STUDENT);

            if ($request->hasFile('profile_picture')) {
                $user['dp'] = $request->file('profile_picture')->store('dp');
            }

            $user->save();
            $newUserData = ['name' => $request->name, 'unhashedPass' => $request->password];
            //$newUserData
            // $studentPhone = Str::of($userData['country_code'])->append($userData['dial_code'])->append($userData['phone'])->value();

            SendEmail::dispatch($request->email, new WelcomeMail($newUserData));

            $message = __('Welcome, :name, you have successfully signed up!, Please login at :link', [
                'name' => $userData['name'],
                'link' => route('login'),
            ]);
            // SendSMS::dispatch($studentPhone, $message);
            return response(["user" => $user], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function updateStudentBio()
    {
        request()->validate([
            'bio' => 'required|max:250',
        ]);
        try {
            if (Auth::user()->type === Role::ROLE_STUDENT) {
                $student = Student::find(Auth::user()->id);
                if ($student->active_status == true) {
                    $student['bio'] = request()?->bio;
                    $student->update();
                    return response(new StudentAPIResource($student));
                } else {
                    return response()->json(['error' => 'Student is currently disabled, please contact admin.', 419]);
                }
            } else {
                return response()->json(['error' => 'Unauthorized', 401]);
            }
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }

    public function userEmailVerified($id)
    {
        $user = Student::find($id);
        if ($user->email_verified_at) {
            $user->email_verified_at = null;
            $user->save();
            return redirect()->back()->with('success', __('User email unverified successfully.'));
        } else {
            $user->email_verified_at = Carbon::now();
            $user->save();
            return redirect()->back()->with('success', __('User email verified successfully.'));
        }
    }

    public function userPhoneVerified($id)
    {
        $user = Student::find($id);
        if ($user->phone_verified_at) {
            $user->phone_verified_at = null;
            $user->save();
            return redirect()->back()->with('success', __('User phone unverified successfully.'));
        } else {
            $user->phone_verified_at = Carbon::now();
            $user->save();
            return redirect()->back()->with('success', __('User phone verified successfully.'));
        }
    }

    public function userStatus(Request $request, $id)
    {
        $user  = Student::find($id);
        $input = ($request->value == "true") ? 1 : 0;
        if ($user) {
            $user->active_status = $input;
            $user->save();
        }
        return response()->json([
            'is_success' => true,
            'message'    => __('User status changed successfully.'),
        ]);
    }
    public function getAllUsers()
    {
        try {
            if (Auth::user()->active_status == true) {
                return StudentAPIResource::collection(Student::where('active_status', true)->orderBy(request()->get('sortKey', 'created_at'), request()->get('sortOrder', 'desc'))->paginate(request()->get('perPage')));
            } else {
                return response()->json(['error' => 'Unauthorized', 401]);
            }
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }
    public function importfun(Request $request)
    {
        if (Auth::user()->can('create-instructors')) {
            if (Auth::user()->type == 'Admin') {
                Excel::import(new StudentsImport(), $request->file('file'));

                $imported = Excel::toArray(new StudentsImport(), $request->file('file'));
                foreach ($imported[0] as $import) {
                    SendEmail::dispatch($import['email'], new WelcomeMail($import));
                    $message = __('Welcome, :name, you have successfully signed up!, Please login at :link', [
                        'name' => $import['name'],
                        'link' => route('login'),
                    ]);
                    $studentPhone = Str::of($import['country_code'])->append($import['dial_code'])->append($import['phone'])->value();
                    SendSMS::dispatch($studentPhone, $message);
                }

                return redirect()->route('student.index')->with('success', __('Students imported successfully.'));
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function updateProfilePicture(Request $request)
    {{
        try {
            $request->validate([
                'dp' => 'required',
            ]);
            if ($request->hasFile('dp') && Auth::user()->type === Role::ROLE_INFLUENCER) {
                $student       = Student::find(Auth::user()?->id);
                $student['dp'] = $request->file('dp')->store('dp');
                $student->update();
                return response()->json([
                    'student' => $student,
                    'message' => 'Profile Picture has been successfully updated',
                ], 201);
            }
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error', 'message' => $e->getMessage()], 500);
        }
    }}
}
