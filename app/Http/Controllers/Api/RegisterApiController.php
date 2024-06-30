<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterApiController extends Controller
{
    /**
     * Tangani permintaan yang masuk.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {   
        // Set aturan validasi
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|max:255|unique:users',
            'password'    => 'required|string|min:8|confirmed',
            'no_whatsapp' => ['required', 'string', 'regex:/^\+62\d{8,12}$/'],
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 400);
        }
        // Mulai transaksi
        DB::beginTransaction();

        try {

            $user = new User();
            $user->id = (string) Str::uuid();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->save();
           
            $user->assignRole('user');
           
            //Buat Pelanggan
            $pelanggan = new Pelanggan();
            $pelanggan->user_id = $user->id;
            $pelanggan->no_whatsapp = $request->no_whatsapp;
            $pelanggan->save();
            
            // Commit transaksi jika semua operasi berhasil
            DB::commit();

            // Kembalikan respons jika user dan pelanggan berhasil dibuat
           
            return response()->json([
                'success'  => true,
                'user'     => $user,
                'customer' => $pelanggan,
            ], 200);

        } catch (\Exception $e) {
            // Batalkan transaksi jika terjadi kesalahan
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat pengguna atau pelanggan',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
