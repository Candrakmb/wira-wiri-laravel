<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Haversine;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfilApiController extends Controller
{

    public function statusDriverKedai(){
        $user = auth()->guard('api')->user();

        if ($user->hasRole('kedai')) {
            $status = Kedai::where('user_id', $user->id)->first();
        } else if($user->hasRole('driver')){
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
    public function statusOnOff(Request $request){

        $validator = Validator::make($request->all(), [
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false,'title' => 'Error', 'icon' => 'error', 'text' => 'Validasi gagal. ' . $validator->errors()->first(), 'ButtonColor' => '#EF5350', 'type' => 'error'], 400);
        }

        $user = auth()->guard('api')->user();

        if ($user->hasRole('kedai')) {
            $kedai = Kedai::where('user_id', $user->id)->update([
                'status' => $request->status,
            ]);

            return response()->json([
                'success' => true,
                'status' => $kedai,
            ], 200);

        } elseif ($user->hasRole('driver')) {
            $driver = Driver::where('user_id', $user->id)->update([
                'status' => $request->status,
                'time_on' => $request->status == 1 ? Carbon::now() : null,
                'latitude' => $request->status == 1 ? $request->latitude : null,
                'longitude' => $request->status == 1 ? $request->longitude : null,
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

        if ($user->hasRole('driver')) {
            $rules = array_merge($rules, [
                'tanggal_lahir' => 'required|date',
                'jenis_kelamin' => 'required',
                'alamat' => 'required|string|max:255',
                'no_plat' => 'required|string|unique:drivers,no_plat,' .  $user->id . ',user_id',
            ]);
        } elseif ($user->getRoleNames()->contains('kedai')) {
            $rules = array_merge($rules,[
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
            $img = '';
            $oldImage = '';
            $userId = $user->id;
            $role = $user->getRoleNames()[0];

            // Update user basic information
            User::where('id', $userId)->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            // Handle image upload
            if ($request->hasFile('profil')) {
                $placeImg = match ($role) {
                    'user' => 'public/image/pelanggan',
                    'driver' => 'public/image/driver',
                    default => 'public/image/kedai',
                };

                foreach ($request->file('profil') as $profil) {
                    $profil_menu = Str::uuid() . '.' . $profil->extension();
                    $profil->storeAs($placeImg, $profil_menu);
                }

                $img = $profil_menu;
            }

            // Update specific role-based data
            $oldImageField = '';
            $roleData = [
                'no_whatsapp' => $request->no_whatsapp,
                'img_profil' => $img
            ];

            switch ($role) {
                case 'user':
                    $pelanggan = Pelanggan::where('user_id', $userId)->first();
                    $oldImage = $pelanggan?->img_profil;
                    Pelanggan::where('user_id', $userId)->update($roleData);
                    break;

                case 'driver':
                    $driver = Driver::where('user_id', $userId)->first();
                    $oldImage = $driver?->img_profil;
                    $roleData = array_merge($roleData, [
                        'tanggal_lahir' => $request->tanggal_lahir,
                        'jenis_kelamin' => $request->jenis_kelamin,
                        'alamat' => $request->alamat,
                        'no_plat' => $request->no_plat,
                    ]);
                    Driver::where('user_id', $userId)->update($roleData);
                    break;

                default:
                    $kedai = Kedai::where('user_id', $userId)->first();
                    $oldImage = $kedai?->img_profil;
                    $roleData = [
                        'alamat' => $request->alamat,
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                        'no_whatsapp' => $request->no_whatsapp,
                        'img' => $img,
                    ];
                    Kedai::where('user_id', $userId)->update($roleData);
            }

            DB::commit();

            // Delete old image if exists
            if ($oldImage) {
                Storage::delete("{$placeImg}/{$oldImage}");
            }

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data',
                'error' => $e->getMessage()
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
    public function selectedAlamat(Request $request){
        $user = auth()->guard('api')->user();
        $pelanggan = Pelanggan::where('user_id', $user->id)->first();
        if($pelanggan){
            $alamat = AlamatPelanggan::with(['pelanggan'])->where('pelanggan_id',$pelanggan->id)->get();
            if($alamat != null){
                $userLatitude = $request->input('latitude');
                $userLongitude = $request->input('longitude');

                $closestAlamatId = null;
                $minDistance = PHP_INT_MAX;

                foreach ($alamat as $item) {
                    $distance = Haversine::calculateDistance($userLatitude, $userLongitude, $item->latitude, $item->longitude);
                    if ($distance < $minDistance) {
                        $minDistance = $distance;
                        $closestAlamatId = $item->id;
                    }
                }
            }else{
                $closestAlamatId = null;
            }

            return response()->json([
                'success' => true,
                'alamat' => $closestAlamatId,
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
            $alamat = AlamatPelanggan::create([
                'pelanggan_id' => $pelanggan->id,
                'alamat' => $request->alamat,
                'tipe_alamat' => $request->tipe_alamat,
                'detail_alamat' => $request->detail_alamat,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);


            DB::commit();
            return response()->json([
                'success' => true,
                'alamat' => $alamat,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
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
           $alamat = AlamatPelanggan::where('id',$request->id)->update([
               'alamat' => $request->alamat,
               'tipe_alamat' => $request->tipe_alamat,
               'detail_alamat' => $request->detail_alamat,
               'latitude' => $request->latitude,
               'longitude' => $request->longitude,
           ]);

           DB::commit();
           return response()->json([
               'success' => true,
               'alamat' => $alamat,
           ], 200);
       } catch (\Exception $e) {
           DB::rollback();
           return response()->json([
               'success' => false,
               'message' => $e->getMessage(),
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
                'success' => true,
               'message' => 'Data alamat berhasil dihapus',
            ], 200);
        } catch(\Exception $e) {
            DB::rollback();
            return response()->json([
               'success' => false,
               'message' => $e->getMessage(),
            ], 500);
        }
    }
}
