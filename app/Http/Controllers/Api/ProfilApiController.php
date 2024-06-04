<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AlamatPelanggan;
use App\Models\Driver;
use App\Models\Kedai;
use App\Models\Pelanggan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProfilApiController extends Controller
{

    public function statusDriverKedai(){
        $user = auth()->guard('api')->user();
        if ($user->getRoleNames()->contains('kedai')) {
            $status = Kedai::where('user_id', $user->id)->first();
        }if($user->getRoleNames()->contains('driver')){
            $status = Driver::where('user_id', $user->id)->first();
        }else {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'status' => $status->status,
        ], 200);
    }
    public function statusOnOff(Request $request, $status){
        $user = auth()->guard('api')->user();
        $statusValue = $status == 'on' ? '1' : '0';
        
        if ($user->getRoleNames()->contains('kedai')) {
            $kedai = Kedai::where('user_id', $user->id)->update([
                'status' => $statusValue,
            ]);
        
            return response()->json([
                'success' => true,
                'status' => $kedai,
            ], 200);
        
        } elseif ($user->getRoleNames()->contains('driver')) {
            $driver = Driver::where('user_id', $user->id)->update([
                'status' => $statusValue,
                'time_on' => $status == 'on' ? Carbon::now() : null,
                'latitude' => $status == 'on' ? $request->latitude : null,
                'longitude' => $status == 'on' ? $request->longitude : null,
            ]);
        
            return response()->json([
                'success' => true,
                'status' => $driver,
            ], 200);
        
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }
        
    }

    public function get_profil(){
        $userDetail = User::where('id', auth()->guard('api')->user()->id)->first();
        if ($userDetail) {
            // Load only the non-empty relationships
            $userDetail->load(['pelanggan', 'driver', 'kedai']);
    
            // Filter out empty relationships
            $filteredUser = $userDetail->toArray();
            $filteredUser = array_filter($filteredUser, function($value, $key) {
                return !is_array($value) || count($value) > 0;
            }, ARRAY_FILTER_USE_BOTH);

            // Kembalikan respon JSON dengan data user dan relasi yang ada
            return response()->json([
                'success' => true,
                'user' => $filteredUser,
                'role'=> $userDetail->getRoleNames(),
            ], 200);
        } else {
            // Kembalikan respon JSON dengan pesan error jika user tidak ditemukan
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }
    }

    public function update_profil(Request $request){
        $user = auth()->guard('api')->user();
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'no_whatsapp' => ['required', 'string', 'regex:/^\+62\d{8,12}$/'],
        ];
        
        if ($user->getRoleNames()->contains('driver')) {
            $rules = array_merge($rules, [
                'tanggal_lahir' => 'required|date',
                'jenis_kelamin' => 'required',
                'alamat' => 'required|string|max:255',
                'no_plat' => 'required|string|unique:drivers,no_plat,' . $user->id,
            ]);
        } elseif ($user->getRoleNames()->contains('kedai')) {
            $rules = array_merge($rules, [
                'alamat' => 'required|string|max:255',
                'latitude' => 'required',
                'longitude' => 'required',
            ]);
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return response()->json([
                'title' => 'Error',
                'icon' => 'error',
                'text' => 'Validasi gagal. ' . $validator->errors()->first(),
                'ButtonColor' => '#EF5350',
                'type' => 'error'
            ], 400);
        }
        
        DB::beginTransaction();
        
        try {
            User::where('id', $user->id)->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);
        
            if ($user->getRoleNames()->contains('user')) {
                Pelanggan::where('user_id', $user->id)->update([
                    'no_whatsapp' => $request->no_whatsapp,
                ]);
            } elseif ($user->getRoleNames()->contains('driver')) {
                Driver::where('user_id', $user->id)->update([
                    'no_whatsapp' => $request->no_whatsapp,
                    'tanggal_lahir' => $request->tanggal_lahir,
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'alamat' => $request->alamat,
                    'no_plat' => $request->no_plat,
                ]);
            } else {
                Kedai::where('user_id', $user->id)->update([
                    'no_whatsapp' => $request->no_whatsapp,
                    'alamat' => $request->alamat,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ]);
            }
        
            DB::commit();
            return response()->json([
                'title' => 'Success!',
                'icon' => 'success',
                'text' => 'Data Berhasil Ditambah!',
                'ButtonColor' => '#66BB6A',
                'type' => 'success'
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'title' => 'Error',
                'icon' => 'error',
                'text' => $e->getMessage(),
                'ButtonColor' => '#EF5350',
                'type' => 'error'
            ], 500);
        }
    }

    public function getAlamat(){
        $user = auth()->guard('api')->user();
        $pelanggan = Pelanggan::where('user_id', $user->id)->first();
        if($pelanggan){
            $alamat = AlamatPelanggan::with(['pelanggan'])->where('pelanggan_id',$pelanggan->id)->get();

            return response()->json([
                'success' => true,
                'alamat' => $alamat,
            ], 200);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'alamat not found',
            ], 404);
        }
        
    }
    public function createAlamat(Request $request){
        $user = auth()->guard('api')->user();

        $validator = Validator::make($request->all(), [
            'alamat' => 'required|string|max:255',
            'tipe_alamat' => 'required|string|max:255',
            'detail_alamat' => 'required|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'title' => 'Error', 
                'icon' => 'error', 
                'text' => 'Validasi gagal. ' . $validator->errors()->first(), 
                'ButtonColor' => '#EF5350', 
                'type' => 'error'
            ], 400);
        }

        $pelanggan = Pelanggan::where('user_id', $user->id)->first();
        DB::beginTransaction();
         try {
            AlamatPelanggan::create([
                'pelanggan_id' => $pelanggan->id,
                'alamat' => $request->alamat,
                'tipe_alamat' => $request->tipe_alamat,
                'detail_alamat' => $request->detail_alamat,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

        
            DB::commit();
            return response()->json([
                'title' => 'Success!',
                'icon' => 'success',
                'text' => 'Data Berhasil Ditambah!',
                'ButtonColor' => '#66BB6A',
                'type' => 'success'
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'title' => 'Error',
                'icon' => 'error',
                'text' => $e->getMessage(),
                'ButtonColor' => '#EF5350',
                'type' => 'error'
            ], 500);
        }
        
    }
    public function getAlamatUpdate($id){

        $alamat = AlamatPelanggan::with(['pelanggan'])->where('id',$id)->first();
        if($alamat){
            return response()->json([
                'success' => true,
                'alamat' => $alamat,
            ], 200);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'alamat not found',
            ], 404);
        }
    }
    public function updateAlamat(Request $request){
        $validator = Validator::make($request->all(), [
            'alamat' => 'required|string|max:255',
            'tipe_alamat' => 'required|string|max:255',
            'detail_alamat' => 'required|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'title' => 'Error', 
                'icon' => 'error', 
                'text' => 'Validasi gagal. ' . $validator->errors()->first(), 
                'ButtonColor' => '#EF5350', 
                'type' => 'error'
            ], 400);
        }

        DB::beginTransaction();
        try {
           AlamatPelanggan::where('id',$request->id)->update([
               'alamat' => $request->alamat,
               'tipe_alamat' => $request->tipe_alamat,
               'detail_alamat' => $request->detail_alamat,
               'latitude' => $request->latitude,
               'longitude' => $request->longitude,
           ]);

           DB::commit();
           return response()->json([
               'title' => 'Success!',
               'icon' => 'success',
               'text' => 'Data Berhasil Ditambah!',
               'ButtonColor' => '#66BB6A',
               'type' => 'success'
           ], 200);
       } catch (\Exception $e) {
           DB::rollback();
           return response()->json([
               'title' => 'Error',
               'icon' => 'error',
               'text' => $e->getMessage(),
               'ButtonColor' => '#EF5350',
               'type' => 'error'
           ], 500);
       }

    }
    public function deleteAlamat($id){
        DB::beginTransaction();

        try {
            $alamat = AlamatPelanggan::where('id', $id)->first();

            if (!$alamat) {
                throw new \Exception('Data tidak ditemukan.');
            }
            $alamat->delete();

            DB::commit();
            return response()->json([
                'title' => 'Success!',
                'icon' => 'success',
                'text' => 'Data Berhasil Dihapus!',
                'ButtonColor' => '#66BB6A',
                'type' => 'success'
            ], 200);
        } catch(\Exception $e) {
            DB::rollback();
            return response()->json([
                'title' => 'Error',
                'icon' => 'error',
                'text' => $e->getMessage(),
                'ButtonColor' => '#EF5350',
                'type' => 'error'
            ], 500);
        }
    }
}
