<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    public $data = [
        'title' => 'Order',
        'modul' => 'order',
    ];
    
    function order(){
        $this->data['type'] = "index";
        $this->data['data'] = null;
    	return view($this->data['modul'].'.index', $this->data);
    }

    function lihat(){
        $this->data['type'] = "lihat";
    	return view($this->data['modul'].'.index', $this->data);
    }


    function table(){
        $query = Order::with(['pelanggan','driver'])
                ->orderBy('kedais.id','desc');
        $query = $query->get();
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                $btn = '';
                $btn .= '<div class="text-center">';
                $btn .= '<div class="btn-group btn-group-solid mx-3">';
                $btn .= '<a class="btn btn-warning ml-1" href="/menu/update/'.$row->id.'" title="Update"><i class="fa fa-edit"></i></a> &nbsp';
                $btn .= '<button class="btn btn-danger btn-raised btn-xs" id="btn-hapus" title="hapus semua menu"><i class="fa fa-trash"></i></button>';
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
    
}
