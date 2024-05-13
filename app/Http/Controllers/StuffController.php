<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\Stuff;
use Illuminate\Http\Request;

class StuffController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        try {
            $data = Stuff::with('stuffStock')->get();

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|min:3',
                'category' => 'required',
            ]);

            $prosesData = Stuff::create([
                'name' => $request->name,
                'category' => $request->category,
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
            $data = Stuff::where('id', $id)->first();

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'name' => 'required',
                'category' => 'required',
            ]);

            $checkProsess = Stuff::where('id', $id)->update([
                'name' => $request->name,
                'category' => $request->category,
            ]);

            if ($checkProsess) {
                $data = Stuff::where('id', $id)->first();
                return ApiFormatter::sendResponse(200, 'success', $data);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $stuff = Stuff::where('id', $id)->doesntHave('inboundStuffs')->first();

            if (!$stuff) {
                return ApiFormatter::sendResponse(400, 'bad request', 'Tidak dapat menghapus data stuff, sudah terdapat data inbound!');
            }
            $checkProsess = $stuff->delete();

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
            $data = Stuff::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, 'success', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function restore($id)
    {
        try {
            // restore : mengambalikan data spesifik yg dihapus/menghapus deleted_at nya
            $checkRestore = Stuff::onlyTrashed()->where('id', $id)->restore();

            if ($checkRestore) {
                $data = Stuff::where('id', $id)->first();
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
            $checkPermanentDelete = Stuff::onlyTrashed()->where('id', $id)->forceDelete();

            if ($checkPermanentDelete) {
                return ApiFormatter::sendResponse(200, 'success', 'Berhasil menghapus permanent data stuff!');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }
}
