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
use Illuminate\Support\Str;

class MenuApiController extends Controller
{
    public function get_kedai(Request $request){

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

        // Sortir kedai berdasarkan jarak terdekat
        $kedai = $kedai->sortBy('distance')->values();

        return response()->json([
            'success'  => true,
            'kedai'    => $kedai,
        ], 200);
    }

    public function get_menu($id)
        {
            $kedai = Kedai::with(['user'])->where('id', $id)->first();
            $menu = Menu::with(['customOptions', 'kategori','customOptions.menuDetail'])->where('kedai_id', $id)->orderBy('menus.id', 'desc')->get();

            if (!$menu) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memuat data',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'kedai' => $kedai,
                'menu' => $menu,
            ], 200);
        }

        public function get_menu_with_kategori($id){

            $kedai = Kedai::with(['user'])->where('id', $id)->first();

            // Ambil menu dengan relasi kategori
            $menu = Menu::with(['customOptions', 'customOptions.menuDetail', 'kategori'])
                        ->where('kedai_id', $id)
                        ->orderBy('menus.id', 'desc')
                        ->get();

            if (!$menu) {
                return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat data',
                 ], 500);
             }


            // Kelompokkan menu berdasarkan kategori
            $menuWithKategori = $menu->groupBy(function($item) {
                return $item->kategori->nama; // 'nama' adalah kolom dari kategori
            });

            return response()->json([
                'success' => true,
                'kedai' => $kedai,
                'menu' => $menuWithKategori
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
        $menu = Menu::with(['customOptions', 'kategori','customOptions.menuDetail'])->where('id',$id)->first();
        $kategori_menu = KategoriPilihMenu::with('menuDetail')->where('menu_id',$id)->get();
        return response()->json([
            'success'  => true,
            'menu'     => $menu,
            'detail_menu' => $kategori_menu,
        ], 200);
    }

    public function createform(Request $request)
    {

        $rules = [
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga' => 'required|numeric',
            'status' => 'required|integer',
            'gambar' => 'required',
            'kategori' => 'required|integer',
            'nama_kategori.*' => 'nullable|string|max:255',
            'opsi.*' => 'nullable|integer',
            'new_kategori_id.*' => 'nullable|integer',
            'max_pilih.*' => 'nullable|integer',
            'nama_pilihan.*' => 'nullable|string|max:255',
            'stok_pilihan.*' => 'nullable|integer',
            'harga_pilihan.*' => 'nullable|numeric',
            'status_pilihan.*' => 'nullable|integer',
            'mark_id_kategori.*' => 'nullable|integer',
        ];

        // Cek validasi awal
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal. ' . $validator->errors()->first(),
            ], 201);
        }

        // Jika ada data tambahan 'nama_kategori', lakukan validasi tambahan
        if (!empty($request->nama_kategori)) {
            $rulesAddOn = [
                'nama_kategori.*' => 'required|string|max:255',
                'opsi.*' => 'required|integer',
                'new_kategori_id.*' => 'required|integer',
                'max_pilih.*' => 'required|integer',
                'nama_pilihan.*' => 'required|string|max:255',
                'stok_pilihan.*' => 'required|integer',
                'harga_pilihan.*' => 'required|numeric',
                'status_pilihan.*' => 'required|integer',
                'mark_id_kategori.*' => 'required|integer',
            ];

            $validatorAddOn = Validator::make($request->all(), $rulesAddOn);

            if ($validatorAddOn->fails()) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal. ' . $validatorAddOn->errors()->first(),
                ], 201);
            }
        }

                // return response()->json([
                //     'success' => false,
                //     'message' => $request->all(),
                // ], 201);
        DB::beginTransaction();
        try {
            $user = auth()->guard('api')->user();
            $kedai = Kedai::where('user_id', $user->id)->first();
            $kedai_id = $kedai->id;

                if ($request->hasFile('gambar')) {
                    foreach ($request->file('gambar') as $gambar) {
                        $gambar_menu = Str::uuid() . '.' . $gambar->extension();
                        $gambar->storeAs('public/image/menu', $gambar_menu);
                    }
                }
                // return response()->json([
                //     'success' => false,
                //     'message' => $request->file('gambar'),
                // ], 201);
                $menu = new Menu();
                $menu->kedai_id = $kedai_id;
                $menu->nama = $request->nama;
                $menu->kategori_id = $request->kategori;
                $menu->deskripsi = $request->deskripsi;
                $menu->harga = $request->harga;
                $menu->status = $request->status;
                $menu->gambar = $gambar_menu;
                $menu->save();

                    $kategori = $request->only(['nama_kategori', 'opsi', 'new_kategori_id' ,'max_pilih']);
                    if (isset($kategori['nama_kategori']) && count($kategori['nama_kategori']) != 0) {
                        foreach ($kategori['nama_kategori'] as $pilihKategori => $value) {

                            $kategoriPilihan = new KategoriPilihMenu();
                            $kategoriPilihan->menu_id = $menu->id;
                            $kategoriPilihan->nama = $kategori['nama_kategori'][$pilihKategori];
                            $kategoriPilihan->opsi = $kategori['opsi'][$pilihKategori];
                            $kategoriPilihan->max_pilih = $kategori['max_pilih'][$pilihKategori];
                            $kategoriPilihan->save();

                            $dataPilihan = $request->only(['nama_pilihan', 'stok_pilihan', 'harga_pilihan', 'status_pilihan', 'mark_id_kategori']);
                            foreach ($dataPilihan['nama_pilihan'] as $pilih => $value) {
                                if ($dataPilihan['mark_id_kategori'][$pilih] == $kategori['new_kategori_id'][$pilihKategori]) {
                                    $detail = new MenuDetail();
                                    $detail->kategori_pilih_menu_id = $kategoriPilihan->id;
                                    $detail->nama_pilihan = $dataPilihan['nama_pilihan'][$pilih];
                                    $detail->harga = $dataPilihan['harga_pilihan'][$pilih];
                                    $detail->status = $dataPilihan['status_pilihan'][$pilih];
                                    $detail->stok = $dataPilihan['stok_pilihan'][$pilih];
                                    $detail->save();
                                }
                            }
                    }
                }



            DB::commit();
            return response()->json(['success' => true, 'massage' => 'menu berhasil ditambah'], 200);
        } catch (\Exception $e) {
            if ($menu->gambar) {
                Storage::delete('public/image/menu/' . $menu->gambar);
            }
            DB::rollback();
            return response()->json([ 'success'=> false, 'massage' => $e->getMessage()], 500);
        }
    }

    public function updateform(Request $request){

        $rules = [
            'menu_id' => 'required|integer',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga' => 'required|numeric',
            'status' => 'required|integer',
            'gambar' => 'nullable',
            'kategori' => 'required|integer',
            'kategori_detail_id.*' => 'nullable|integer',
            'nama_kategori.*' => 'nullable|string|max:255',
            'opsi.*' => 'nullable|integer',
            'max_pilih.*' => 'nullable|integer',
            'id_detail.*' => 'nullable|integer',
            'nama_pilihan.*' => 'nullable|string|max:255',
            'stok_pilihan.*' => 'nullable|integer',
            'harga_pilihan.*' => 'nullable|numeric',
            'status_pilihan.*' => 'nullable|integer',
            'mark_id_kategori.*' => 'nullable|integer',
        ];

        // Cek validasi awal
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal. ' . $validator->errors()->first(),
            ], 201);
        }

        // Jika ada data tambahan 'nama_kategori', lakukan validasi tambahan
        if (!empty($request->nama_kategori)) {
            $rulesAddOn = [
                'kategori_detail_id.*' => 'nullable|integer',
                'nama_kategori.*' => 'required|string|max:255',
                'opsi.*' => 'required|integer',
                'max_pilih.*' => 'required|integer',
                'id_detail.*' => 'nullable|integer',
                'nama_pilihan.*' => 'required|string|max:255',
                'stok_pilihan.*' => 'required|integer',
                'harga_pilihan.*' => 'required|numeric',
                'status_pilihan.*' => 'required|integer',
                'mark_id_kategori.*' => 'required|integer',
            ];

            $validatorAddOn = Validator::make($request->all(), $rulesAddOn);

            if ($validatorAddOn->fails()) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal. ' . $validatorAddOn->errors()->first(),
                ], 201);
            }
        }
        // dd($request->all());
        DB::beginTransaction();
        $user = auth()->guard('api')->user();
        $kedai = Kedai::where('user_id', $user->id)->first();
        try {
            $oldImage = null;
            $kedai_id = $kedai->id;
            $menu = Menu::findOrFail($request->menu_id);
            $menu->kedai_id = $kedai_id;
            $menu->nama = $request->nama;
            $menu->kategori_id = $request->kategori;
            $menu->deskripsi = $request->deskripsi;
            $menu->harga = $request->harga;
            $menu->status = $request->status;

            if ($request->hasFile('gambar')) {


                foreach ($request->file('gambar') as $gambar) {
                    $gambar_menu = Str::uuid() . '.' . $gambar->extension();
                    $gambar->storeAs('public/image/menu', $gambar_menu);
                }

                if ($menu->gambar) {
                    $oldImage =  $menu->gambar;
                }
                $menu->gambar = $gambar_menu;
            }

            $menu->save();


                $existingKategoriMenuDetail = KategoriPilihMenu::where('menu_id', $request->menu_id)->get();

                if (!empty($existingKategoriMenuDetail)) {
                    $existingKategoriMenu =$existingKategoriMenuDetail->pluck('id')->toArray();
                    $existingMenuDetail = MenuDetail::whereIn('kategori_pilih_menu_id', $existingKategoriMenu)->pluck('id')->toArray();
                    KategoriPilihMenu::where('menu_id', $request->menu_id)->delete();
                }

                $kategoriData = $request->only(['nama_kategori', 'opsi', 'kategori_detail_id','max_pilih']);

                if (!empty($kategoriData['kategori_detail_id'])) {

                foreach ($kategoriData['nama_kategori'] as $index => $value) {
                    $kategoriPilihan = new KategoriPilihMenu();

                    if (isset($kategoriData['kategori_detail_id'][$index]) && in_array($kategoriData['kategori_detail_id'][$index], $existingKategoriMenu)) {
                        $kategoriPilihan->id = $kategoriData['kategori_detail_id'][$index];
                    }

                    $kategoriPilihan->menu_id = $request->menu_id;
                    $kategoriPilihan->nama = $kategoriData['nama_kategori'][$index];
                    $kategoriPilihan->opsi = $kategoriData['opsi'][$index];
                    $kategoriPilihan->max_pilih=$kategoriData['max_pilih'][$index];
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
            if ($oldImage) {
                Storage::delete('public/image/menu' . $oldImage);
            }
            return response()->json([
                'success' => true,
                'massage' => 'menu berhasil diupdate',
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'massage' => $e->getMessage(),
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
                'success'  => true,
                'massage' => 'menu berhasil dihapus',
            ], 200);
        } catch(\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'massage' => $e->getMessage(),
            ], 500);
        }
    }
}
