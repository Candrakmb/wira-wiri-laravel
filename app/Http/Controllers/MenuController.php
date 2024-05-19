<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\KategoriPilihan;
use App\Models\KategoriPilihMenu;
use App\Models\Kedai;
use App\Models\Menu;
use App\Models\MenuDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class MenuController extends Controller
{
    public $data = [
        'title' => 'Menu',
        'modul' => 'menu',
    ];
    
    function menu(){
        $this->data['type'] = "index";
        $this->data['data'] = null;
        $this->data['kedai_maps'] = Kedai::with(['user'])->get();
    	return view($this->data['modul'].'.index', $this->data);
    }

    function create(){
        $this->data['type'] = "create";
        $this->data['data'] = null;
        $this->data['kedai'] = Kedai::whereNotIn('id', function ($query) {
            $query->select('kedai_id')->from('menus');
        })->with(['user'])->get();        
        $this->data['kategori'] = Kategori::get();
    	return view($this->data['modul'].'.index', $this->data);
    }

    function update($id){
        $this->data['type'] = "update";
        $this->data['data'] = null;
        $this->data['kedai'] = Kedai::with(['user'])->where('id', $id)->first();
        $this->data['menu'] = Menu::where('kedai_id', $id)->get();
        $this->data['kategoriMenu']= KategoriPilihMenu::get();
        $this->data['menu_detail'] = MenuDetail::get();
        $this->data['kategori'] = Kategori::get();
    	return view($this->data['modul'].'.index', $this->data);
    }

    function lihat(){
        $this->data['type'] = "lihat";
    	return view($this->data['modul'].'.index', $this->data);
    }


    function table(){
        $query = Kedai::with(['user'])
                ->orderBy('kedais.id','desc');
        $query = $query->get();
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                $cek_jumlah = Menu::where('kedai_id', $row->id)->count();
                $btn = '';
                $btn .= '<div class="text-center">';
                $btn .= '<div class="btn-group btn-group-solid mx-3">';
                if ($cek_jumlah != 0) {
                $btn .= '<a class="btn btn-warning ml-1" href="/menu/update/'.$row->id.'" title="Update"><i class="fa fa-edit"></i></a> &nbsp';
                $btn .= '<button class="btn btn-danger btn-raised btn-xs" id="btn-hapus" title="hapus semua menu"><i class="fa fa-trash"></i></button>';
                }
                $btn .= '</div>';    
                $btn .= '</div>';
                return $btn;
            })
            ->addColumn('jumlah_menu', function($row){
                $jumlah = Menu::where('kedai_id', $row->id)->count();
                $jml = '';
                $jml .= '<div class="text-center">';
                $jml .= '<div class="btn-group btn-group-solid mx-3">';
                $jml .= '<p>'.$jumlah.'</p>';
                $jml .= '</div>';    
                $jml .= '</div>';
                return $jml;
            })
            ->addColumn('status_name', function($row){
                $status = ''; 
                $status .= '<div class="text-center">';
                $status .= '<div class="btn-group btn-group-solid mx-3">';
                if ($row->status == 0) {
                    $status .= '<div class="badge rounded-pill bg-danger">Tutup</div>';
                }
                if ($row->status == 1) {
                    $status .= '<div class="badge rounded-pill bg-success">Buka</div>';
                }
                $status .= '</div>';    
                $status .= '</div>';
                return $status;
            })
            ->rawColumns(['action','jumlah_menu','status_name'])
            ->make(true);
    }
    
    public function createform(Request $request)
    {
        DB::beginTransaction();
    
        try {
                $kedai_id = $request->kedai_id;
                $data = $request->only(
                    [
                        'nama',
                        'deskripsi',
                        'harga',
                        'status',
                        'gambar',
                        'kategori',
                        'menu_kedai_id',
                        'menu_id',
                    ]
                );

                if (isset($data['nama'])) {
                    foreach ($data['nama'] as $key => $value) {
                            $gambar_menu = time() . '.' . $data['gambar'][$key]->extension();
                            $data['gambar'][$key]->storeAs('public/image/menu', $gambar_menu);
                            $menu_id = $data['menu_kedai_id'][$key];
                            $menu = new Menu();
                            $menu->kedai_id=$kedai_id;
                            $menu->nama=$data['nama'][$key];
                            $menu->kategori_id=$data['kategori'][$key];
                            $menu->deskripsi=$data['deskripsi'][$key];
                            $menu->harga=$data['harga'][$key];
                            $menu->status=$data['status'][$key];
                            $menu->gambar=$gambar_menu;
                            $menu->save();

                            if(isset($data['menu_id'])){

                                $katetgori = $request->only(
                                    [
                                        'nama_kategori',
                                        'opsi',
                                        'new_id_kategori',
                                        'menu_id',
                                    ]
                                );
                                foreach ($katetgori['nama_kategori'] as $pilihKategori => $value) {
                                    if($katetgori['menu_id'][$pilihKategori] == $menu_id){
                                        $katetgoriPilihan = new KategoriPilihMenu();
                                        $katetgoriPilihan->menu_id=$menu->id;
                                        $id_kategori=$katetgori['new_id_kategori'][$pilihKategori];
                                        $katetgoriPilihan->nama=$katetgori['nama_kategori'][$pilihKategori];
                                        $katetgoriPilihan->opsi=$katetgori['opsi'][$pilihKategori];
                                        $katetgoriPilihan->save();

                                        $dataPilihan = $request->only(
                                            [
                                                'nama_pilihan',
                                                'stok_pilihan',
                                                'harga_pilihan',
                                                'status_pilihan',
                                                'mark_id_kategori',
                                            ]
                                        );
                                        foreach ($dataPilihan['nama_pilihan'] as $pilih => $value) {
                                            if($dataPilihan['mark_id_kategori'][$pilih] == $id_kategori){
                                                $detail = new MenuDetail();
                                                $detail->kategori_pilih_menu_id=$katetgoriPilihan->id;
                                                $detail->nama_pilihan=$dataPilihan['nama_pilihan'][$pilih];
                                                $detail->harga=$dataPilihan['harga_pilihan'][$pilih];
                                                $detail->status=$dataPilihan['status_pilihan'][$pilih];
                                                $detail->stok=$dataPilihan['stok_pilihan'][$pilih];
                                                $detail->save();
                                            }
                                        }
                                        
                                    }

                                }
                                
                               
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
            
                $kedai_id = $request->kedai_id;
                $data = $request->only(
                    [
                        'nama',
                        'deskripsi',
                        'harga',
                        'status',
                        'gambar',
                        'oldImg',
                        'kategori',
                        'menu_kedai_id',
                        'menu_id',
                        'id',
                    ]
                );
                $cek_id = [];
                
                if (isset($data['nama'])) {
                    
                    $existingMenusId = Menu::where('kedai_id', $request->kedai_id)->pluck('id')->toArray();

                    $existingKategoriMenu = KategoriPilihMenu::whereIn('menu_id', $existingMenusId)->pluck('id')->toArray();
                    
                    $existingMenuDetail = MenuDetail::whereIn('kategori_pilih_menu_id', $existingKategoriMenu)->pluck('id')->toArray();
                    
                    $cek_id = $data['id'];
                    
                    $itemsToDelete = array_diff($existingMenusId, $cek_id);
                    foreach ($itemsToDelete as $menuId) {
                        $menu_delete = Menu::find($menuId);
                        if ($menu_delete && $menu_delete->gambar) {
                            Storage::delete('public/image/menu/' . $menu_delete->gambar);
                        }
                    }
                    if (!empty($existingMenusId)) {
                        Menu::where('kedai_id', $request->kedai_id)->delete();
                    }
                    foreach ($data['nama'] as $key => $value) {
                            $menu = new Menu();
                            $menu_id = $data['menu_kedai_id'][$key];
                            if (isset($data['id'][$key]) && in_array($data['id'][$key], $existingMenusId)) {
                                $menu->id = $data['id'][$key];
                                $menu_update_id = $data['id'][$key];
                            }
                            $menu->kedai_id=$kedai_id;
                            $menu->nama=$data['nama'][$key];
                            $menu->kategori_id=$data['kategori'][$key];
                            $menu->deskripsi=$data['deskripsi'][$key];
                            $menu->harga=$data['harga'][$key];
                            $menu->status=$data['status'][$key];

                            if (!empty($data['gambar'][$key])) {
                                // Hapus gambar lama jika ada
                                if (isset($data['oldImg'][$key])) {
                                    Storage::delete('public/image/menu/' . $data['oldImg'][$key]);
                                }
            
                                // Simpan gambar baru
                                $gambar_menu = time() . '.' . $data['gambar'][$key]->extension();
                                $data['gambar'][$key]->storeAs('public/image/menu', $gambar_menu);
                                $menu->gambar =  $gambar_menu;
                            }else{
                                $menu->gambar =$data['oldImg'][$key];
                            }
                            $menu->save();
                    
                        if (isset($data['menu_id'])) {
                            $kategori = $request->only(['nama_kategori', 'opsi', 'new_id_kategori', 'menu_id', 'kategori_id']);

                            // if (!empty($existingKategoriMenu)) {
                            //     KategoriPilihMenu::whereIn('id', $existingKategoriMenu)->delete();
                            // }
                            
                            foreach ($kategori['nama_kategori'] as $pilihKategori => $value) {
                                if ($kategori['menu_id'][$pilihKategori] == $menu_id) {
                                    $kategoriPilihan = new KategoriPilihMenu();
                                    if (isset($kategori['kategori_id'][$pilihKategori]) && in_array($kategori['kategori_id'][$pilihKategori], $existingKategoriMenu)) {
                                            $kategoriPilihan->id = $kategori['kategori_id'][$pilihKategori];
                                            $update_kategori_id = $kategori['kategori_id'][$pilihKategori];
                                    }
                                    $kategoriPilihan->menu_id=$menu->id;
                                    $id_kategori=$kategori['new_id_kategori'][$pilihKategori];
                                    $kategoriPilihan->nama=$kategori['nama_kategori'][$pilihKategori];
                                    $kategoriPilihan->opsi=$kategori['opsi'][$pilihKategori];
                                    $kategoriPilihan->save();
                    
                                    $dataPilihan = $request->only(['nama_pilihan', 'stok_pilihan', 'harga_pilihan', 'status_pilihan', 'mark_id_kategori', 'id_detail']);
                                    // if (!empty($existingMenuDetail)) {
                                    //     MenuDetail::whereIn('id', $existingMenuDetail)->delete();
                                    // }
                                    
                                    foreach ($dataPilihan['nama_pilihan'] as $pilih => $value) {
                                        if ($dataPilihan['mark_id_kategori'][$pilih] == $id_kategori) {
                                            $detail = new MenuDetail();
                                            if (isset($datapilihan['id_detail'][$pilih]) && in_array($datapilihan['id_detail'][$pilih], $existingMenuDetail)) {
                                                $detail->id = $datapilihan['detail_id'][$pilih];
                                            }
                                            $detail->kategori_pilih_menu_id=$kategoriPilihan->id;
                                            $detail->nama_pilihan=$dataPilihan['nama_pilihan'][$pilih];
                                            $detail->harga=$dataPilihan['harga_pilihan'][$pilih];
                                            $detail->status=$dataPilihan['status_pilihan'][$pilih];
                                            $detail->stok=$dataPilihan['stok_pilihan'][$pilih];
                                            $detail->save();
                                        }
                                    }
                                }
                            }
                        }
                    }                    
                }
                DB::commit();
                return response()->json(['title' => 'Success!', 'icon' => 'success', 'text' => 'Data Berhasil Diubah!', 'ButtonColor' => '#66BB6A', 'type' => 'success']);
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
            $kedai = Kedai::findOrFail($request->id); 

            $menus = Menu::where('kedai_id', $kedai->id)->get();
            
            foreach ($menus as $menu) {
                // Menghapus gambar jika ada
                if ($menu->gambar) {
                    Storage::delete('public/image/menu/' . $menu->gambar);
                }
            
                // Menghapus menu dari database
                $menu->delete();
            }
    
            DB::commit();
            return response()->json(['title' => 'Success!', 'icon' => 'success', 'text' => 'Data Berhasil Dihapus!', 'ButtonColor' => '#66BB6A', 'type' => 'success']); 
        } catch(\Exception $e) {
            DB::rollback();
            return response()->json(['title' => 'Error', 'icon' => 'error', 'text' => $e->getMessage(), 'ButtonColor' => '#EF5350', 'type' => 'error']); 
        }   
    }
}
