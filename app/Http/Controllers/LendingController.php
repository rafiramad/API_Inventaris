<?php

namespace App\Http\Controllers;

use App\Models\StuffStock;
use Illuminate\Http\Request;
use App\Helpers\ApiFormatter;
use App\Models\Lending;


class LendingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index() {
        try {
            //kalo ada with cek nya itu di relasinya yg ada di model sebelum with, ambil nama functionnya
            $data = Lending::with('stuff', 'user', 'restoration')->get();
            
                return ApiFormatter::sendResponse(200, 'succes', $data);
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function store(Request $request) {
        try {
            $this->validate($request, [
                'stuff_id' => 'required',
                'date_time' => 'required',
                'name' => 'required',
                'total_stuff' => 'required',
            ]);
            //user_id tidak masuk ke validasi karena valuenya bukan bersumber dari luar (dipilih user)

            //cek total_available stuff terkait
            $totalAvailable = StuffStock::where('stuff_id', $request->stuff_id)->value('total_available');

            if (is_null($totalAvailable)) {
                return ApiFormatter::sendResponse(400, 'bad request', 'Belum ada data inbound !');
            } elseif ((int)$request->total_stuff > (int)$totalAvailable) {
                return ApiFormatter::sendResponse(400, 'bad request', 'Stock tidak tersedia !');
            } else {
                $lending = Lending::create([
                    'stuff_id' => $request->stuff_id,
                    'date_time' => $request->date_time,
                    'name' => $request->name,
                    'notes' => $request->notes ? $request->notes : '-',
                    'total_stuff' => $request->total_stuff,
                    'user_id' => auth()->user()->id,
                ]);

                $totalAvailableNow = (int)$totalAvailable - (int)$request->total_stuff;
                $StuffStock = StuffStock::where('stuff_id', $request->stuff_id)->update(['total_available' => $totalAvailableNow]);

                $dataLending = Lending::where('id', $lending['id'])->with('user', 'stuff', 'stuff.StuffStock')->first();

                return ApiFormatter::sendResponse(200, 'succes', $dataLending);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
       }
    
       
    }
    public function destroy($id) 
    {
        try {

            $dataLending = Lending::where('id', $id,)->first();

            if ($dataLending) {
                // return ApiFormatter::sendResponse(200, 'success', $dataLending);
                if($dataLending->restoration) {
                    return ApiFormatter::sendResponse(400, 'bad request', 'Data peminjaman sudah memiliki data pengambil');
                } else {
                    $StuffStock=StuffStock::where('stuff_id', $dataLending->stuff_id)->first();
                    $StuffStock->update(['total_avaliable' => $dataLending->tota_stuff+$StuffStock->total_avaliable]);
                    $dataLending->delete();
                    return ApiFormatter::sendResponse(200, 'success', $dataLending);
                }
                
            } else {
                return ApiFormatter::sendResponse(404, 'Data not found');
            }
        }  catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());

}
}
}