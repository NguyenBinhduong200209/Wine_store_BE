<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'getUser', 'editUser']]);
    }

    //
    function getData()
    {
        return User::all();
    }
    function register(Request $req)
    {
        // Check if the username already exists
        $existingUsername = User::where('username', $req->input('username'))->first();
        if ($existingUsername) {
            return response()->json(['message' => 'Tên người dùng đã tồn tại'], 422);
        }

        // Check if the email already exists
        $existingEmail = User::where('email', $req->input('email'))->first();
        if ($existingEmail) {
            return response()->json(['message' => 'Email đã tồn tại'], 422);
        }

        // If neither username nor email exists, proceed with user registration

        $user = new User;
        $user->username = $req->input('username');
        $user->fullname = $req->input('fullname');
        $user->password = Hash::make($req->input('password'));
        $user->email = $req->input('email');
        $user->role = $req->input('role');
        $user->created_at = Carbon::now();
        $user->updated_at = $user->created_at;
        $user->save();


        return response()->json(['message' => 'Đăng ký thành công', 'user' => $user], 200);
    }

    function editUser(Request $req)
    {
        $user = $req->user;

        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại'], 404);
        }

        // Cập nhật thông tin người dùng
        $user->fill($req->only([
            'username',
            'email',
            // thêm các trường cần cập nhật ở đây
        ]));

        // Kiểm tra nếu có dữ liệu mới cho password, thì cập nhật password
        if ($req->has('password')) {
            $user->password = Hash::make($req->input('password'));
        }

        $user->updated_at = Carbon::now();
        $user->save();

        // Nếu có mối quan hệ hasOne với UserDetail
        if ($user->userDetail) {
            // Cập nhật thông tin nếu userDetail đã tồn tại
            $user->userDetail->fill($req->only([
                'phone',
                'address',
                // thêm các trường cần cập nhật cho UserDetail ở đây
            ]));

            $user->userDetail->save();

            return response()->json(['message' => 'Cập nhật thông tin người dùng thành công', 'user' => $user, 'user_detail' => $user->userDetail], 200);
        } else {
            // Nếu userDetail chưa tồn tại, tạo mới
            $userDetail = $user->userDetail()->create($req->only([
                'phone',
                'address',
                // thêm các trường cần tạo mới cho UserDetail ở đây
            ]));

            return response()->json(['message' => 'Cập nhật thông tin người dùng và tạo mới UserDetail thành công', 'user' => $user, 'user_detail' => $userDetail], 200);
        }
    }



    function deleteUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'Xóa người dùng thành công'], 200);
    }

    function getUser(Request $req)
    {
        $user = $req->user;

        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại'], 404);
        }

        return $user;
    }
    function login(Request $req)
    {
        $credentials = $req->only('username', 'password');

        if (Auth::attempt($credentials)) {
            // Authentication passed
            $user = Auth::user();

            $token = JWTAuth::fromUser($user); // Tạo token cho người dùng

            return response()->json([
                'message' => 'Đăng nhập thành công', // Thêm message ở đây
                'user' => $user,
                'token' => $token,
            ]);
        } else {
            // Authentication failed
            return response()->json(['message' => 'Sai tên đăng nhập hoặc mật khẩu'], 401);
        }
    }



    protected function respondWithToken($token)
    {
        $tokenTTL = JWTAuth::factory()->getTTL();

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $tokenTTL * 60 // Chuyển thời gian sống của token về giây
        ]);
    }
}
