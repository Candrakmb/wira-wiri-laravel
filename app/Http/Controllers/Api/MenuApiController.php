<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Haversine;
use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\KategoriPilihMenu;
use App\Models\Kedai;
use App\Models\Menu;
use App\Models\MenuDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class MenuApiController extends Controller
{
    public function get_kedai(Request $request){

        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $search = $request->search;
        // dd($search);
        // Dapatkan kedai dengan status aktif
        $kedaiQuery = Kedai::with(['user', 'menu'])->where('status', '1');

        // Jika ada parameter pencarian, tambahkan kondisi pencarian ke query
        if ($search) {
            $kedaiQuery->where(function ($queryBuilder) use ($search) {
                $queryBuilder->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'LIKE', "%{$search}%"); // Pencarian di nama user (nama kedai)
                })->orWhereHas('menu', function ($menuQuery) use ($search) {
                    $menuQuery->where('nama', 'LIKE', "%{$search}%"); // Pencarian di nama menu
                });
            });
        }

        $kedai = $kedaiQuery->get();

        if ($kedai->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada kedai yang ditemukan',
            ], 404);
        }

        // Hitung jarak dan tambahkan ke objek kedai
        $kedaiWithDistance = $kedai->map(function ($k) use ($latitude, $longitude) {
            $k->distance = Haversine::calculateDistance($latitude, $longitude, $k->latitude, $k->longitude);
            return $k;
        });

        // Sortir kedai berdasarkan jarak terdekat
        $kedaiWithDistance = $kedaiWithDistance->sortBy('distance')->values();

        return response()->json([
            'success'  => true,
            'kedai'    => $kedaiWithDistance,
        ], 200);
    }

    public function get_menu($id){
        $kedai = Kedai::with(['user'])->where('id',  $id)->first();
        $menu = Menu::with(['kategori_menu','kategori'])->where('kedai_id',  $id)->orderBy('menus.id','desc')->get();
        if(!$menu){
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat data',
            ], 500);
        }

        return response()->json([
            'success'  => true,
            'kedai'    => $kedai,
            'menu'     => $menu,
        ], 200);

    }
    public function get_menu_detail($id){
        $pilihan = MenuDetail::where('kategori_pilih_menu_id ',  $id)->get();

        if(!$pilihan){
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat data',
            ], 500);
        }

        return response()->json([
            'success'  => true,
            'menu'     => $pilihan,
        ], 200);
    }

    public function create(){
        $kategori = Kategori::get();
        return response()->json([
            'success'  => true,
            'kategori'     => $kategori,
        ], 200);
    }

    public function update($id){
        $menu = Menu::with(['kategori'])->where('id',$id)->first();
        $kategori_menu = KategoriPilihMenu::with('menuDetail')->where('menu_id',$id)->get();
        return response()->json([
            'success'  => true,
            'menu'     => $menu,
            'detail_menu' => $kategori_menu,
        ], 200);
    }

    public function createform(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'kedai_id' => 'required',
            'nama.*' => 'required|string|max:255',
            'deskripsi.*' => 'required|string',
            'harga.*' => 'required|numeric',
            'status.*' => 'required|integer',
            'gambar.*' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'kategori.*' => 'required|integer',
            'menu_kedai_id.*' => 'required|integer',
            'menu_id.*' => 'nullable|integer',
            'nama_kategori.*' => 'required|string|max:255',
            'opsi.*' => 'required|integer',
            'new_id_kategori.*' => 'nullable|integer',
            'nama_pilihan.*' => 'required|string|max:255',
            'stok_pilihan.*' => 'required|integer',
            'harga_pilihan.*' => 'required|numeric',
            'status_pilihan.*' => 'required|integer',
            'mark_id_kategori.*' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            DB::rollback();
            return response()->json(['title' => 'Error', 'icon' => 'error', 'text' => 'Validasi gagal. ' . $validator->errors()->first(), 'ButtonColor' => '#EF5350', 'type' => 'error'], 400);
        }

        DB::beginTransaction();
        try {
            $user = auth()->guard('api')->user();
            $kedai = Kedai::where('user_id', $user->id)->first();
            $kedai_id = $kedai->id;
            $data = $request->only(['nama', 'deskripsi', 'harga', 'status', 'gambar', 'kategori', 'menu_kedai_id', 'menu_id']);

            foreach ($data['nama'] as $key => $value) {
                $gambar_menu = time() . '.' . $data['gambar'][$key]->extension();
                $data['gambar'][$key]->storeAs('public/image/menu', $gambar_menu);
                $menu = new Menu(); 
                $menu->kedai_id = $kedai_id;
                $menu->nama = $data['nama'][$key];
                $menu->kategori_id = $data['kategori'][$key];
                $menu->deskripsi = $data['deskripsi'][$key];
                $menu->harga = $data['harga'][$key];
                $menu->status = $data['status'][$key];
                $menu->gambar = $gambar_menu;
                $menu->save();

                if (isset($data['menu_id'][$key])) {
                    $katetgori = $request->only(['nama_kategori', 'opsi', 'new_id_kategori', 'menu_id']);
                    foreach ($katetgori['nama_kategori'] as $pilihKategori => $value) {
                        if ($katetgori['menu_id'][$pilihKategori] == $data['menu_kedai_id'][$key]) {
                            $katetgoriPilihan = new KategoriPilihMenu();
                            $katetgoriPilihan->menu_id = $menu->id;
                            $katetgoriPilihan->nama = $katetgori['nama_kategori'][$pilihKategori];
                            $katetgoriPilihan->opsi = $katetgori['opsi'][$pilihKategori];
                            $katetgoriPilihan->save();

                            $dataPilihan = $request->only(['nama_pilihan', 'stok_pilihan', 'harga_pilihan', 'status_pilihan', 'mark_id_kategori']);
                            foreach ($dataPilihan['nama_pilihan'] as $pilih => $value) {
                                if ($dataPilihan['mark_id_kategori'][$pilih] == $katetgori['new_id_kategori'][$pilihKategori]) {
                                    $detail = new MenuDetail();
                                    $detail->kategori_pilih_menu_id = $katetgoriPilihan->id;
                                    $detail->nama_pilihan = $dataPilihan['nama_pilihan'][$pilih];
                                    $detail->harga = $dataPilihan['harga_pilihan'][$pilih];
                                    $detail->status = $dataPilihan['status_pilihan'][$pilih];
                                    $detail->stok = $dataPilihan['stok_pilihan'][$pilih];
                                    $detail->save();
                                }
                            }
                        }
                    }
                }
            }
            DB::commit();
            return response()->json(['title' => 'Success!', 'icon' => 'success', 'text' => 'Data Berhasil Ditambah!', 'ButtonColor' => '#66BB6A', 'type' => 'success'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['title' => 'Error', 'icon' => 'error', 'text' => $e->getMessage(), 'ButtonColor' => '#EF5350', 'type' => 'error'], 500);
        }
    }

    public function updateform(Request $request){
        
        $validator = Validator::make($request->all(), [
            'kedai_id' => 'required',
            'menu_id' => 'required|integer',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga' => 'required|numeric',
            'status' => 'required|integer',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'kategori' => 'required|integer',
            'kategori_detail_id.*' => 'nullable|integer',
            'nama_kategori.*' => 'required|string|max:255',
            'opsi.*' => 'required|integer',
            'id_detail.*' => 'nullable|integer',
            'nama_pilihan.*' => 'required|string|max:255',
            'stok_pilihan.*' => 'required|integer',
            'harga_pilihan.*' => 'required|numeric',
            'status_pilihan.*' => 'required|integer',
            'mark_id_kategori.*' => 'nullable|integer',
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
        // dd($request->all());
        DB::beginTransaction();
        $user = auth()->guard('api')->user();
        $kedai = Kedai::where('user_id', $user->id)->first();
        try {
            $kedai_id = $kedai->id;
            $menu = Menu::findOrFail($request->menu_id);
            $menu->kedai_id = $kedai_id;
            $menu->nama = $request->nama;
            $menu->kategori_id = $request->kategori;
            $menu->deskripsi = $request->deskripsi;
            $menu->harga = $request->harga;
            $menu->status = $request->status;
        
            if ($request->hasFile('gambar')) {
                if ($menu->gambar) {
                    Storage::delete('public/image/menu/' . $menu->gambar);
                }
        
                $gambar_menu = time() . '.' . $request->gambar->extension();
                $request->gambar->storeAs('public/image/menu', $gambar_menu);
                $menu->gambar = $gambar_menu;
            }
        
            $menu->save();
        

                $existingKategoriMenuDetail = KategoriPilihMenu::where('menu_id', $request->menu_id)->get();
               
                if (!empty($existingKategoriMenuDetail)) {
                    $existingKategoriMenu =$existingKategoriMenuDetail->pluck('id')->toArray();
                    $existingMenuDetail = MenuDetail::whereIn('kategori_pilih_menu_id', $existingKategoriMenu)->pluck('id')->toArray();
                    KategoriPilihMenu::where('menu_id', $request->menu_id)->delete();
                }
               
                $kategoriData = $request->only(['nama_kategori', 'opsi', 'kategori_detail_id']);
                
                if (!empty($kategoriData['kategori_detail_id'])) {

                foreach ($kategoriData['nama_kategori'] as $index => $value) {
                    $kategoriPilihan = new KategoriPilihMenu();
        
                    if (isset($kategoriData['kategori_detail_id'][$index]) && in_array($kategoriData['kategori_detail_id'][$index], $existingKategoriMenu)) {
                        $kategoriPilihan->id = $kategoriData['kategori_detail_id'][$index];
                    }
        
                    $kategoriPilihan->menu_id = $request->menu_id;
                    $kategoriPilihan->nama = $kategoriData['nama_kategori'][$index];
                    $kategoriPilihan->opsi = $kategoriData['opsi'][$index];
                    $kategoriPilihan->save();
        
                    $dataPilihan = $request->only(['nama_pilihan', 'stok_pilihan', 'harga_pilihan', 'status_pilihan', 'mark_id_kategori','id_detail']);
        
                    foreach ($dataPilihan['nama_pilihan'] as $pilihIndex => $value) {
                        if ($dataPilihan['mark_id_kategori'][$pilihIndex] == $kategoriData['kategori_detail_id'][$index]) {
                            $detail = new MenuDetail();
                            if (isset($dataPilihan['id_detail'][$pilihIndex]) && in_array($dataPilihan['id_detail'][$pilihIndex], $existingMenuDetail)) {
                                $detail->id = $dataPilihan['id_detail'][$pilihIndex];
                            }
        
                            $detail->kategori_pilih_menu_id = $kategoriPilihan->id;
                            $detail->nama_pilihan = $dataPilihan['nama_pilihan'][$pilihIndex];
                            $detail->harga = $dataPilihan['harga_pilihan'][$pilihIndex];
                            $detail->status = $dataPilihan['status_pilihan'][$pilihIndex];
                            $detail->stok = $dataPilihan['stok_pilihan'][$pilihIndex];
                            $detail->save();
                        }
                    }
                }
            }
        
            DB::commit();
        
            return response()->json([
                'title' => 'Success!',
                'icon' => 'success',
                'text' => 'Data Berhasil Ditambah!',
                'ButtonColor' => '#66BB6A',
                'type' => 'success',
                'data' => $request->all()
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
    public function deleteform($id){

        DB::beginTransaction();

        try {
            $menu = Menu::where('id', $id)->first();

            if (!$menu) {
                throw new \Exception('Data tidak ditemukan.');
            }

            if ($menu->gambar) {
                Storage::delete('public/image/menu/' . $menu->gambar);
            }

            $menu->delete();

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
