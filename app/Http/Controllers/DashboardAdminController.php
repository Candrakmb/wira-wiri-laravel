<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Kedai;
use App\Models\Order;
use App\Models\Pelanggan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DashboardAdminController extends Controller
{
    public $data = [
        'title' => 'Dashboard',
        'modul' => 'dashboardAdmin',
    ];

    public function index()
    {
        $this->data['tahunIni'] = now()->year;
         // Data utama
        $this->data['jumlahPelanggan'] = User::role('user')->count();
        $this->data['jumlahPelangganBaru'] = User::role('user')
                                                ->whereDate('created_at', Carbon::today())
                                                ->count();
        $this->data['jumlahDriver'] = User::role('driver')->count();
        $this->data['jumlahKedai'] = User::role('kedai')->count();
        $this->data['jumlahOrder'] = Order::count();
        $this->data['jumlahOrderBaru'] = Order::whereDate('created_at', Carbon::today())->count();
        $this->data['jumlahDriverOn'] = Driver::where('status', 1)->count();
        $this->data['jumlahKedaiBuka'] = Kedai::where('status', 1)->count();
        $this->data['jumlahOrderProse']= Order::whereBetween('status_order', [1, 6])
                ->whereDate('created_at', Carbon::today())
                ->count();

        $labels = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        // Ambil data pesanan per bulan di tahun ini
        $orderDataDone = Order::selectRaw('MONTHNAME(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', now()->year)
            ->where('status_order', '7')
            ->groupByRaw('MONTH(created_at), MONTHNAME(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->get();
        $orderDataDeny = Order::selectRaw('MONTHNAME(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', now()->year)
            ->where('status_order', '8')
            ->groupByRaw('MONTH(created_at), MONTHNAME(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->get();
        $orderPendapatan = Order::selectRaw('MONTHNAME(created_at) as month, SUM(ongkir) as total_revenue')
            ->whereYear('created_at', now()->year) // Filter berdasarkan tahun ini
            ->where('status_order', '8') // Status order 8
            ->groupByRaw('MONTH(created_at), MONTHNAME(created_at)') // Kelompokkan berdasarkan bulan
            ->orderByRaw('MONTH(created_at)') // Urutkan berdasarkan bulan
            ->get();

        // Susun data untuk grafik
        $dataOrderDone = [];
        $dataOrderDeny = [];
        $dataPendapatan = [];
        foreach ($labels as $label) {
            $monthOrders = $orderDataDone->firstWhere('month', $label);
            $dataOrderDone[] = $monthOrders ? $monthOrders->total : 0; // Jika tidak ada pesanan, set 0
        }
        foreach ($labels as $label) {
            $monthOrders = $orderDataDeny->firstWhere('month', $label);
            $dataOrderDeny[] = $monthOrders ? $monthOrders->total : 0; // Jika tidak ada pesanan, set 0
        }
        foreach ($labels as $label) {
            $monthOrders = $orderPendapatan->firstWhere('month', $label);
            $dataPendapatan[] = $monthOrders ? $monthOrders->total_revenue : 0; // Jika tidak ada pesanan, set 0
        }

        $this->data['chartDataDone'] = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'datasets' => [
                [
                    'label'=> "Mobile apps",
                    'tension'=> 0,
                    'borderWidth'=> 0,
                    'pointRadius'=> 5,
                    'pointBackgroundColor'=> "rgba(255, 255, 255, .8)",
                    'pointBorderColor'=> "transparent",
                    'borderColor'=> "rgba(255, 255, 255, .8)",
                    'borderWidth'=> 4,
                    'backgroundColor'=> "transparent",
                    'fill'=> true,
                    'data' => $dataOrderDone,
                    'maxBarThickness' => 6
                ]
            ]
        ];

        $this->data['chartDataDeny'] = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'datasets' => [
                [
                    'label'=> "Mobile apps",
                    'tension'=> 0,
                    'borderWidth'=> 0,
                    'pointRadius'=> 5,
                    'pointBackgroundColor'=> "rgba(255, 255, 255, .8)",
                    'pointBorderColor'=> "transparent",
                    'borderColor'=> "rgba(255, 255, 255, .8)",
                    'borderWidth'=> 4,
                    'backgroundColor'=> "transparent",
                    'fill'=> true,
                    'data' => $dataOrderDeny,
                    'maxBarThickness' => 6
                ]
            ]
        ];

        $this->data['chartDataPendapatan'] = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'datasets' => [
                [
                    'label'=> "Mobile apps",
                    'tension'=> 0,
                    'borderWidth'=> 0,
                    'pointRadius'=> 5,
                    'pointBackgroundColor'=> "rgba(255, 255, 255, .8)",
                    'pointBorderColor'=> "transparent",
                    'borderColor'=> "rgba(255, 255, 255, .8)",
                    'borderWidth'=> 4,
                    'backgroundColor'=> "transparent",
                    'fill'=> true,
                    'data' => $dataPendapatan,
                    'maxBarThickness' => 6
                ]
            ]
        ];

        return view($this->data['modul'], $this->data);
    }

    function table() {
        $orders = Order::whereBetween('status_order', [1, 6])
                ->whereDate('created_at', Carbon::today())
                ->orderBy('created_at', 'asc')
                ->get();
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
                    '1' => '<div class="badge rounded-pill bg-secondary">mendapatkan driver</div>',
                    '2' => '<div class="badge rounded-pill bg-secondary">menuju kedai</div>',
                    '3' => '<div class="badge rounded-pill bg-secondary">menuju kedai</div>',
                    '4' => '<div class="badge rounded-pill bg-secondary">menuju kedai</div>',
                    '5' => '<div class="badge rounded-pill bg-secondary">driver menunggu dikedai</div>',
                    '6' => '<div class="badge rounded-pill bg-secondary">mengantar</div>',
                ];
                $status = isset($orderStatus[$row->status_order]) ?
                    $orderStatus[$row->status_order] :
                    '<div class="badge rounded-pill bg-secondary">tidak diketahui</div>';
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
