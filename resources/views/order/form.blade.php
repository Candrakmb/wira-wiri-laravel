<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12">
          <div class="card card-plus" >
            <div class="card-header bg-gradient-info p-2">
                  <a href="{{ url()->previous()}}" >
                      <button class="button_back float-start ms-2">
                          <i class="fa fa-arrow-left text-white svgIcon_back"></i>
                      </button>
                  </a>
                  <h6 class="text-center text-white mt-2">INVOICE</h6>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card card-plus my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-info shadow-info border-radius-lg">
                            <div
                                class="row p-3"
                            >
                                <div class="col-md-6 text-white text-uppercase ps-3">invoice</div>
                                <div class="col-md-6  ps-3 "><h6 class="text-white text-end">No Order : {{$order->invoice_number}}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div class="p-3 mb-2 bg-white font-sans-serif">
                        <div
                            class="row p-3"
                        >
                        <div class="col-md-12">
                            <p class="text-muted fs-6 text-center">Dibuat : {{tanggal($order->created_at)}}</p>
                        </div>
                            <div class="col-md-6">
                                <address>
                                    <strong>Customer :</strong><br>
                                    <div class="text-muted">
                                        {{ $pelanggan->user->name }}<br>
                                        {{ $pelanggan->user->email }}<br>
                                        {{$pelanggan->no_whatsapp}}<br>
                                    </div>
                                    <strong>Tujuan :</strong><br>
                                    @foreach ($orderDestinasi as $alamat)
                                    @if ($alamat->tipe_destination == '0')
                                    <div class="text-muted">
                                        {{ $alamat->alamatpelanggan->alamat.' , '. $alamat->alamatpelanggan->detail_alamat }}
                                    </div>
                                    @endif
                                    @endforeach
                                </address>
                            </div>
                            <div class="col-md-6 text-end">
                                <address>
                                    <strong>Driver :</strong><br>
                                    @if($driver != null)
                                    <div class="text-muted">
                                    {{ $driver->user->name }}<br>
                                    {{ $driver->user->email }}<br>
                                    {{$driver->no_whatsapp}}<br>
                                    </div>
                                    @else
                                    <div class="badge rounded-pill bg-info">Belum dapat</div>
                                    @endif
                                </address>
                            </div>
                            <div class="col-md-6">
                                <strong>Pembayaran :</strong><br>
                                 {!! $order->pembayaran !!}
                            </div>
                            <div class="col-md-6 text-end">
                                <strong>Status :</strong><br>
                                @if ($order->status_order != '')
                                {!! $order->order_status !!}
                                @else
                                <div class="badge rounded-pill bg-info">Proses Pembayaran</div>
                                @endif
                            </div>
                        </div>
                        <div
                            class="row p-3"
                        >
                            <div class="col-md-12">
                                @foreach ($orderDestinasi as $list)
                                @if($list->tipe_destination == '1')
                                <strong>kedai :</strong><br>
                                <div class="text-muted">
                                {{$list->kedai->user->name}}<br>
                                {{$list->kedai->no_whatsapp}}<br>
                                {{$list->kedai->alamat}}<br>
                                </div>
                                <strong>Ringkasan Pesanan :</strong><br>

                                <div class="table-responsive">
                                    <table class="table table-striped table-hover table-md">
                                        <tbody>
                                            <tr>
                                                <th data-width="40" style="width: 40px;">#</th>
                                                <th>nama</th>
                                                <th class="text-center">harga</th>
                                                <th class="text-center">qty</th>
                                                <th class="text-right">Total</th>
                                            </tr>
                                            @foreach ($orderDetail as $detail)
                                                @if($detail->menu->kedai_id == $list->kedai_id)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $detail->menu->nama }}<br>
                                                        @foreach ($detail->orderekstra as $ekstra)
                                                        {{$ekstra->nama_ekstra}} : {{$ekstra->menudetail->nama_pilihan}}<br>
                                                        @endforeach
                                                     </td>
                                                    <td class="text-center">
                                                        {{ rupiah($detail->menu->harga) }}
                                                    </td>
                                                    <td class="text-center">{{ $detail->qty }}</td>
                                                    <td class="text-right">
                                                        {{ rupiah($detail->price) }}</td>
                                                </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                                @endforeach
                            </div>
                            <div class="col-md-6"></div>
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-borderless table-md">
                                        <tbody>
                                                <tr>
                                                    <td class="text-right">Subtotal</td>
                                                    <td data-width="40" style="width: 40px;">:</td>
                                                    <td class="text-right">{{rupiah($order->subtotal)}}</td>
                                                </tr>

                                                <tr>
                                                    <td class="text-right">ongkir</td>
                                                    <td data-width="40" style="width: 40px;">:</td>
                                                    <td class="text-right">{{rupiah($order->ongkir)}}</td>
                                                </tr>
                                                @if($order->metode_pembayaran == '1')
                                                <tr>
                                                    <td class="text-right">Admin</td>
                                                    <td data-width="40" style="width: 40px;">:</td>
                                                    <td class="text-right">{{rupiah(5000)}}</td>
                                                </tr>
                                                @endif
                                                <tr>
                                                    <td class="text-right">total</td>
                                                    <td data-width="40" style="width: 40px;">:</td>
                                                    <td class="text-right">{{rupiah($order->total_pay)}}</td>
                                                </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                   </div>
                </div>
            </div>
        </div>
</div>
