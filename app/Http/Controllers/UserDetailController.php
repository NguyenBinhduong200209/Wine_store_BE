<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserDetail;
use Illuminate\Support\Facades\Validator;

class UserDetailController extends Controller
{
    // Lấy thông tin chi tiết người dùng
    public function getUserDetail($userId)
    {
        $userDetail = UserDetail::where('user_id', $userId)->first();

        if (!$userDetail) {
            return response()->json(['message' => 'Thông tin chi tiết người dùng không tồn tại'], 404);
        }

        return $userDetail;
    }

    // Thêm thông tin chi tiết người dùng mới
    public function addUserDetail(Request $req, $userId)
    {
        $validator = Validator::make($req->all(), [

            'phone' => 'max:20',
            'address' => 'max:100',

        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 400);
        }
        $userid = $req->user->id;
        $userDetail = new UserDetail;
        $userDetail->user_id = $userid;
        $userDetail->phone = $req->input('phone');
        $userDetail->address = $req->input('address');

        $userDetail->save();

        return $userDetail;
    }

    // Sửa thông tin chi tiết người dùng
    public function editUserDetail(Request $req, $userId)
    {
        $userDetail = UserDetail::where('user_id', $userId)->first();

        if (!$userDetail) {
            return response()->json(['message' => 'Thông tin chi tiết người dùng không tồn tại'], 404);
        }

        $validator = Validator::make($req->all(), [

            'phone' => 'max:20',
            'address' => 'max:100',

        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 400);
        }


        $userDetail->phone = $req->input('phone');
        $userDetail->address = $req->input('address');
        $userDetail->save();

        return $userDetail;
    }

    // Xóa thông tin chi tiết người dùng
    public function deleteUserDetail($userId)
    {
        $userDetail = UserDetail::where('user_id', $userId)->first();

        if (!$userDetail) {
            return response()->json(['message' => 'Thông tin chi tiết người dùng không tồn tại'], 404);
        }

        $userDetail->delete();

        return response()->json(['message' => 'Xóa thông tin chi tiết người dùng thành công'], 200);
    }
}
