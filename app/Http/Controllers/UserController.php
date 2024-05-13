<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    
    public function index()
    {
        try {
            $data = User::all()->toArray();

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'username' => 'required|unique:users,username',
                'email' => 'required|unique:users,email',
                'password' => 'required',
                'role' => 'required'
            ]);

            $prosesData = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            if ($prosesData) {
                return ApiFormatter::sendResponse(200, 'success', $prosesData);
            } else {
                return ApiFormatter::sendResponse(400, 'bad request', 'Gagal memproses tambah data stuff! silahkan coba lagi.');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $data = User::where('id', $id)->first();

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'username' => 'required|unique:users,username,' . $id,
                'email' => 'required|unique:users,email,' . $id,
                'password' => 'required',
                'role' => 'required'
            ]);

            $checkProsess = User::where('id', $id)->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            if ($checkProsess) {
                $data = User::where('id', $id)->first();
                return ApiFormatter::sendResponse(200, 'success', $data);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $checkProsess = User::where('id', $id)->delete();

            if ($checkProsess) {
                return ApiFormatter::sendResponse(200, 'success', 'Berhasil hapus data stuff!');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function trash()
    {
        try {
            // onlyTrashed() : memanggil data sampah/yg sudah dihapus/deleted_at nya terisi
            $data = User::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            // restore : mengambalikan data spesifik yg dihapus/menghapus deleted_at nya
            $checkRestore = User::onlyTrashed()->where('id', $id)->restore();

            if ($checkRestore) {
                $data = User::where('id', $id)->first();
                return ApiFormatter::sendResponse(200, 'success', $data);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function permanentDelete($id)
    {
        try {
            // forceDelete() : menghapus permanent (hilang jg data di db nya)
            $checkPermanentDelete = User::onlyTrashed()->where('id', $id)->forceDelete();

            if ($checkPermanentDelete) {
                return ApiFormatter::sendResponse(200, 'success', 'Berhasil menghapus permanent data stuff!');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }
}
