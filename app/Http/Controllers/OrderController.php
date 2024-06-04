<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderDestination;
use App\Models\OrderDetail;
use App\Models\OrderDetailEkstra;
use App\Models\Pelanggan;
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

    function lihat($id){
        $this->data['type'] = "lihat";
        $this->data['data'] = null;
        $this->data['order'] = Order::where('invoice_number',$id)->first();
        $this->data['orderDetail'] = OrderDetail::with(['menu','orderEkstra'])->where('order_id',$this->data['order']->id)->get();
        $this->data['orderDestinasi'] = OrderDestination::with(['alamatPelanggan','kedai'])->where('order_id',$this->data['order']->id)->get();
        // dd($this->data['orderDetail']);
        $this->data['driver'] = Driver::with('user')->where('id',$this->data['order']->driver_id)->first();
        $this->data['pelanggan'] = Pelanggan::with('user')->where('id',$this->data['order']->pelanggan_id)->first();
    	return view($this->data['modul'].'.index', $this->data);
    }


    function table() {
        $orders = Order::orderBy('id', 'desc')->get();
        return DataTables::of($orders)
            ->addIndexColumn()
            ->addColumn('action', function($row) {
                return '<div class="text-center">
                            <div class="btn-group btn-group-solid mx-3">
                                <a class="btn btn-warning ml-1" href="/order/lihat/'.$row->invoice_number.'" title="lihat">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </div>
                        </div>';
            })
            ->addColumn('pembayaran', function($row) {
                $paymentMethod = [
                    '0' => '<div class="badge rounded-pill bg-info">COD</div>',
                    '1' => '<div class="badge rounded-pill bg-primary">Midtrans</div>'
                ];
                $pembayaran = isset($paymentMethod[$row->metode_pembayaran]) ? 
                    $paymentMethod[$row->metode_pembayaran] : 
                    '<div class="badge rounded-pill bg-secondary">Unknown</div>';
                return '<div class="text-center">
                            <div class="btn-group btn-group-solid mx-3">
                                '.$pembayaran.'
                            </div>
                        </div>';
            })
            ->addColumn('status', function($row) {
                $orderStatus = [
                    '0' => '<div class="badge rounded-pill bg-info">Proses</div>',
                    '1' => '<div class="badge rounded-pill bg-primary">Antar</div>',
                    '2' => '<div class="badge rounded-pill bg-success">Selesai</div>',
                    '3' => '<div class="badge rounded-pill bg-danger">Batal</div>'
                ];
                $status = isset($orderStatus[$row->status_order]) ? 
                    $orderStatus[$row->status_order] : 
                    '<div class="badge rounded-pill bg-secondary">Pembayaran</div>';
                return '<div class="text-center">
                            <div class="btn-group btn-group-solid mx-3">
                                '.$status.'
                            </div>
                        </div>';
            })
            ->addColumn('driver', function($row) {
                $driver = $row->driver_id ? 
                    Driver::with('user')->find($row->driver_id)->user->name : 
                    'belum mendapatkan driver';
                return '<div class="text-center">
                            <div class="btn-group btn-group-solid mx-3">
                                <p>'.$driver.'</p>
                            </div>
                        </div>';
            })
            ->addColumn('pelanggan', function($row) {
                $pelanggan = Pelanggan::with('user')->find($row->pelanggan_id)->user->name;
                return '<div class="text-center">
                            <div class="btn-group btn-group-solid mx-3">
                                <p>'.$pelanggan.'</p>
                            </div>
                        </div>';
            })
            ->rawColumns(['action', 'pembayaran', 'status', 'driver', 'pelanggan'])
            ->make(true);
    }
    
}
