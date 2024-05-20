<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Kedai;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public $data = [
        'title' => 'User',
        'modul' => 'user',
    ];
    
    function user(){
        $this->data['type'] = "index";
        $this->data['data'] = null;
        $this->data['driver_maps'] = Driver::whereNot('status','0')->with(['user'])->get();
    	return view($this->data['modul'].'.index', $this->data);
    }

    function create(){
        $this->data['type'] = "create";
        $this->data['data'] = null;
    	return view($this->data['modul'].'.index', $this->data);
    }

    function update($id){
        $this->data['type'] = "update";
        $this->data['data'] = null;
        $this->data['dataUser'] = User::where('id',$id)->first();
        $this->data['pelanggan'] = Pelanggan::where('user_id' , $id)->first();
        $this->data['driver'] = Driver::where('user_id' , $id)->first();
        $this->data['kedai'] = Kedai::where('user_id' , $id)->first();

    	return view($this->data['modul'].'.index', $this->data);
    }

    function lihat(){
        $this->data['type'] = "lihat";
    	return view($this->data['modul'].'.index', $this->data);
    }


    function table(){
        $query = User::with(['driver','kedai','pelanggan'])
                ->orderBy('users.id','desc');
        $query = $query->get();
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                $btn = '';
                $btn .= '<div class="text-center">';
                $btn .= '<div class="btn-group btn-group-solid mx-3">';
                if (!$row->getRoleNames()->contains('user')) {
                    $btn .= '<a class="btn btn-warning ml-1" href="/user/update/'.$row->id.'"><i class="fa fa-edit"></i></a> &nbsp';
                }
                if (!$row->getRoleNames()->contains('admin') && !$row->getRoleNames()->contains('user')) {
                    $btn .= '<button class="btn btn-danger btn-raised btn-xs" id="btn-hapus" title="Hapus"><i class="fa fa-trash"></i></button>';
                }
                $btn .= '</div>';    
                $btn .= '</div>';
                return $btn;
            })
            ->addColumn('role', function($row){
                $status = ''; 
                $status .= '<div class="text-center">';
                $status .= '<div class="btn-group btn-group-solid mx-3">';
                if ($row->getRoleNames()->contains('user')) {
                    $status .= '<div class="badge rounded-pill bg-success">Customer</div>';
                }
                if ($row->getRoleNames()->contains('admin')) {
                    $status .= '<div class="badge rounded-pill bg-primary">Admin</div>';
                }
                if ($row->getRoleNames()->contains('driver')) {
                    $status .= '<div class="badge rounded-pill bg-info">Driver</div>';
                }
                if ($row->getRoleNames()->contains('kedai')) {
                    $status .= '<div class="badge rounded-pill bg-secondary">Kedai</div>';
                }
                $status .= '</div>';    
                $status .= '</div>';
                return $status;
            })
            ->rawColumns(['action','role'])
            ->make(true);
    }
    
    public function createform(Request $request)
    {
        DB::beginTransaction();
    
        try {

                $role = $request->role;
                $data = $request->only(
                    [
                        'email',
                        'name',
                        'role'
                    ]
                );

                if (isset($data['email'])) {
                    foreach ($data['name'] as $key => $value) {
                        // if (!isset($value)) {
                        //     DB::rollback();
                        //     return response()->json([
                        //         'title' => 'Error',
                        //         'icon' => 'error',
                        //         'text' => "Nilai dengan kunci $key tidak di-set",
                        //         'ButtonColor' => '#EF5350',
                        //         'type' => 'error'
                        //     ]);
                        // }
                            if ($role == 'driver'){
                                $dataRole = $request->only(
                                    [
                                        'password',
                                        'no_wa',
                                        'alamat',
                                        'tgl_lhr',
                                        'jenis_kelamin',
                                    ]
                                );
                                $userDriver = new User();
                                $userDriver->id = (string) Str::uuid();
                                $userDriver->name = $data['name'][$key];
                                $userDriver->email = $data['email'][$key];
                                $userDriver->password = Hash::make($dataRole['password'][$key]);
                                $userDriver->save();
                                $userDriver->assignRole('driver');

                                $driver = new Driver();
                                $driver->user_id=$userDriver->id;
                                $driver->id=(string) Str::uuid();
                                $driver->no_whatsapp=$dataRole['no_wa'][$key];
                                $driver->tanggal_lahir = $dataRole['tgl_lhr'][$key];
                                $driver->jenis_kelamin = $dataRole['jenis_kelamin'][$key];
                                $driver->alamat = $dataRole['alamat'][$key];
                                $driver->status= '0';
                                $driver->save();

                                
                            }
                            if ($role == 'kedai'){
                                $dataRole = $request->only(
                                    [
                                        'password',
                                        'no_wa',
                                        'alamat',
                                        'latitude',
                                        'longitud',
                                    ]
                                );
                                $userKedai = new User();
                                $userKedai->id = (string) Str::uuid();
                                $userKedai->name = $data['name'][$key];
                                $userKedai->email = $data['email'][$key];
                                $userKedai->password = Hash::make($dataRole['password'][$key]);
                                $userKedai->save();
                                $userKedai->assignRole('kedai');

                                $kedai = new Kedai();
                                $kedai->user_id=$userKedai->id;
                                $kedai->id=(string) Str::uuid();
                                $kedai->no_whatsapp=$dataRole['no_wa'][$key];
                                $kedai->latitude = $dataRole['latitude'][$key];
                                $kedai->longitude = $dataRole['longitud'][$key];
                                $kedai->alamat = $dataRole['alamat'][$key];
                                $kedai->status= '0';
                                $kedai->save();
                                
                            }
                            if ($role == 'pelanggan') {
                                $dataRole = $request->only(
                                    [
                                        'password',
                                        'no_wa',
                                    ]
                                );
                                $userPelanggan = new User();
                                $userPelanggan->id = (string) Str::uuid();
                                $userPelanggan->name = $data['name'][$key];
                                $userPelanggan->email = $data['email'][$key];
                                $userPelanggan->password = Hash::make($dataRole['password'][$key]);
                                $userPelanggan->save();
                                $userPelanggan->assignRole('user');

                                $pelanggan = new Pelanggan();
                                $pelanggan->user_id=$userPelanggan->id;
                                $pelanggan->id=(string) Str::uuid();
                                $pelanggan->no_whatsapp=$dataRole['no_wa'][$key];
                                $pelanggan->save();
                                
                            }
                            
                        }
                       
                    }
                DB::commit();
                return response()->json(['title' => 'Success!', 'icon' => 'success', 'text' => 'Data Berhasil Ditambah!', 'ButtonColor' => '#66BB6A', 'type' => 'success']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json(['title' => 'Error', 'icon' => 'error', 'text' => 'Validasi gagal. ' . $e->getMessage(), 'ButtonColor' => '#EF5350', 'type' => 'error']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['title' => 'Error', 'icon' => 'error', 'text' => $e->getMessage(), 'ButtonColor' => '#EF5350', 'type' => 'error']);
        }
    }    

    public function updateform(Request $request)
    {
        DB::beginTransaction();

        try {
            // Mengecek apakah nama sudah digunakan selain oleh id yang sedang diperbarui
            $cek = User::where('email', $request->email)
                ->where('id', '!=', $request->id_user)
                ->first();

            // Jika nama belum digunakan selain oleh id yang sedang diperbarui, update data
            if ($cek == null) {
                $role = $request->role;
                $user = User::findOrFail($request->id_user);
                $user->name = $request->name;
                $user->email = $request->email;
                if( $request->password != null){
                    $user->password = $request->password;
                }
                $user->save();
                if($role == 'pelanggan'){
                    $pelanggan = Pelanggan::findOrFail($request->id);
                    $pelanggan->no_whatsapp = $request->no_wa;
                    $pelanggan->save();
                }else if ($role == 'driver'){
                    $driver = Driver::findOrFail($request->id);
                    $driver->no_whatsapp = $request->no_wa;
                    $driver->alamat = $request->alamat;
                    $driver->tanggal_lahir = $request->tanggal_lahir;
                    $driver->jenis_kelamin = $request->jenis_kelamin;
                    $driver->save();
                }else if ($role == 'kedai') {
                    $kedai = Kedai::findOrFail($request->id);
                    $kedai->no_whatsapp = $request->no_wa;
                    $kedai->alamat = $request->alamat;
                    $kedai->latitude = $request->latitude;
                    $kedai->longitude = $request->longitude;
                    $kedai->save();
                }
                DB::commit();
                return response()->json(['title' => 'Success!', 'icon' => 'success', 'text' => 'Data Berhasil Diubah!', 'ButtonColor' => '#66BB6A', 'type' => 'success']);
            } else {
                DB::rollback();
                return response()->json(['title' => 'Error', 'icon' => 'error', 'text' => 'Nama sudah digunakan!', 'ButtonColor' => '#EF5350', 'type' => 'error']);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json(['title' => 'Error', 'icon' => 'error', 'text' => 'Validasi gagal. ' . $e->getMessage(), 'ButtonColor' => '#EF5350', 'type' => 'error']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['title' => 'Error', 'icon' => 'error', 'text' => $e->getMessage(), 'ButtonColor' => '#EF5350', 'type' => 'error']);
        }
    }

    public function deleteform(Request $request)
    {
        DB::beginTransaction();
    
        try {
            User::where('id', $request->id)->delete();
    
            DB::commit();
            return response()->json(['title' => 'Success!', 'icon' => 'success', 'text' => 'Data Berhasil Dihapus!', 'ButtonColor' => '#66BB6A', 'type' => 'success']); 
        } catch(\Exception $e) {
            DB::rollback();
            return response()->json(['title' => 'Error', 'icon' => 'error', 'text' => $e->getMessage(), 'ButtonColor' => '#EF5350', 'type' => 'error']); 
        }   
    }
}
