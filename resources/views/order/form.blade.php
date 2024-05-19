<div class="container-fluid py-4">
   @if ($type == 'update' || $type == 'create')
    <div class="row mb-3">
        <div class="col-12">
          <div class="card card-plus" >
            <div class="card-header bg-gradient-primary p-2">
                  <a href="{{ url()->previous()}}" >
                      <button class="button_back float-start ms-2">
                          <i class="fa fa-arrow-left text-white svgIcon_back"></i>
                      </button>
                  </a>
                  @if ($type == 'create')
                  <h6 class="text-center text-white mt-2">Membuat Daftar Menu Kedai</h6>
                  @endif
                  @if ($type == 'update')
                  <h6 class="text-center text-white mt-2">Menu Kedai {{$kedai->user->name}}</h6>
                  @endif
              </div>
            </div>
          </div>
        </div>
    @endif
    <form id="form-data" method="post" autocompleted="off" enctype="multipart/form-data">
        @csrf
        @if ($type == 'create')
            <div class="row">
                <div class="col-12">
                    <div class="card card-plus my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                <h6 class="text-white text-uppercase ps-3">Kedai</h6>
                            </div>
                        </div>
                        <div class="card-body px-4 pb-3">
                            <div class="row ">
                                <div class="col-md-6 mt-2">
                                    <label for="kedai_id" class="label1">Kedai</label><span class="required">*</span>
                                    <select name="kedai_id" class="form-select select kedai_id">
                                        <option value="">pilih kedai</option>
                                        @foreach ($kedai as $item)
                                            <option value="{{ $item->id }}">{{ $item->user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row card_menu">
            </div>
        @endif
        @if ($type == 'update')
            <div class="row card_menu">
            <input type="hidden" name="kedai_id" value="{{$kedai->id}}">
              @foreach ($menu as $item)
                <div class="col-12">
                    <div class="card card-plus my-4">
                        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-3 pb-2">
                                <div class="row">
                                    <div class="col-md-6 pt-2">
                                        <h6 class="text-white text-capitalize ps-3">{{$item->nama}}</h6>
                                    </div>
                                    <div class="col-md-6 d-flex justify-content-end pe-4">
                                        <button type="button" class="button_trash btn-hapus-detail">
                                            <svg viewBox="0 0 448 512" class="svgIcon">
                                                <path
                                                    d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body px-4 pb-3">
                            <div class="row">
                                <div class="col-md-6 mt-2">
                                    <div class="col-md-12 mt-5">
                                        <div class="d-flex justify-content-center" data-id="{{ $item->id }}">
                                            <div class="container_file">
                                                <div class="header_file img{{ $item->id }}">
                                                  <img src="{{asset('storage/image/menu/'.$item->gambar)}}" alt="{{$item->nama}}">
                                                </div>
                                                <input type="hidden" name="oldImg[]" value="{{$item->gambar}}">
                                                <input type="file" class="form-control fileImg" name="gambar[]">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mt-2">
                                    <div class="col-md-12 mt-2">
                                        <label for="nama[]" class="label1">Nama Menu</label><span
                                            class="required">*</span>
                                        <input type="text" id="nama" placeholder="contoh: pecel" name="nama[]"
                                            id="nama" class="form-control input nama" value="{{$item->nama}}" required>
                                        <input type="hidden" name="id[]" value="{{$item->id}}">
                                        <input type="hidden" name="menu_kedai_id[]" value="{{$item->id}}">
                                        <p class="help-block" style="display: none;"></p>
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label for="kategori[]" class="label1">kategori</label><span
                                            class="required">*</span>
                                        <select name="kategori[]" class="form-select select kategori" required>
                                            <option value="" readonly>--Select--</option>
                                                @foreach ($kategori as $category)
                                                    <option value="{{ $category->id }}" {{ $category->id == $item->kategori_id ? 'selected' : '' }}>{{ $category->nama }}</option>
                                                @endforeach
                                        </select>
                                        <p class="help-block" style="display: none;"></p>
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label for="harga[]" class="label1">harga</label><span
                                            class="required">*</span>
                                        <input type="number" id="harga" value="{{$item->harga}}" placeholder="Rp." name="harga[]"
                                            class="form-control input" required>
                                        <p class="help-block" style="display: none;"></p>
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label for="deskripsi[]" class="label1">deskripsi</label><span
                                            class="required">*</span>
                                        <textarea id="deskripsi" placeholder="contoh: nasi + pecel" name="deskripsi[]" class="form-control input" required>{{$item->deskripsi}}</textarea>
                                        <p class="help-block" style="display: none;"></p>
                                    </div>
                                    <div class="col-md-12 mt-2">
                                        <label for="status[]" class="label1">Status</label><span
                                            class="required">*</span>
                                        <select name="status[]" class="form-select select status" required>
                                            <option value="" readonly>pilih status</option>
                                            <option value="1" {{$item->status == '1' ? 'selected':''}}>Tersedia</option>
                                            <option value="0"{{$item->status == '0' ? 'selected':''}}>Kosong</option>
                                        </select>
                                        <p class="help-block" style="display: none;"></p>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-2" data-id="{{$item->id}}">
                                    <div class="row mt-2">
                                          @foreach ($menu_detail as $detail)
                                          @if ($detail->menu_id == $item->id)
                                          <div class="col-md-6 mt-2 mb-2">
                                          <div class="card card-plus">
                                            <div class="card-header bg-gradient-primary">
                                                <div class="row">
                                            <div class="col-md-6 pt-2">
                                                <h6 class="text-white text-capitalize ps-3">pilihan</h6>
                                            </div>
                                            <div class="col-md-6 d-flex justify-content-end pe-4">
                                                <button class="button_trash btn-hapus-pilihan">
                                            <svg viewBox="0 0 448 512" class="svgIcon"><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path></svg>
                                            </button>
                                            </div>
                                         </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-12 mt-2">
                                                        <label for="nama_tambahan[]" class="label1">Kategori</label><span class="required">*</span>
                                                        <input type="hidden" name="menu_id[]" value="{{$item->id}}">
                                                        <input type="hidden" name="detail_id[]" value="{{$detail->id}}">
                                                        <input type="text" id="nama_tambahan" placeholder="contoh: level" name="nama_tambahan[]" class="form-control input"value="{{$detail->nama_tambahan}}"  required>
                                                        <p class="help-block" style="display: none;"></p>
                                                    </div>
                                                    <div class="col-md-12 mt-2">
                                                        <label for="nama_pilihan[]" class="label1">pilihan</label><span class="required">*</span>
                                                        <input type="text" id="nama_pilihan" placeholder="contoh: pedas" name="nama_pilihan[]" value="{{$detail->nama_pilihan}}" class="form-control input nama_pilihan" required>
                                                        <p class="help-block" style="display: none;"></p>
                                                    </div>
                                                    <div class="col-md-6 mt-2">
                                                        <label for="stok_pilihan[]" class="label1">stok</label><span class="required">*</span>
                                                        <input type="number" id="stok_pilihan" placeholder="contoh: 1" name="stok_pilihan[]" class="form-control input" value="{{$detail->stok}}" required>
                                                        <p class="help-block" style="display: none;"></p>
                                                    </div>
                                                    <div class="col-md-6 mt-2">
                                                        <label for="harga_pilihan[]" class="label1">Harga</label><span class="required">*</span>
                                                        <input type="text" id="harga_pilihan" placeholder="contoh: 1000" value="{{$detail->harga}}" name="harga_pilihan[]" class="form-control input" required>
                                                        <p class="help-block" style="display: none;"></p>
                                                    </div>
                                                    <div class="col-md-12 mt-2">
                                                        <label for="status_pilihan[]" class="label1">Status</label><span class="required">*</span>
                                                        <select name="status_pilihan[]" class="form-select select status_pilihan" required>
                                                            <option value="" readonly>pilih status</option>
                                                            <option value="1" {{$detail->status == '1' ? 'selected':''}}>Tersedia</option>
                                                            <option value="0"{{$detail->status == '0' ? 'selected':''}}>Kosong</option>
                                                        </select>
                                                        <p class="help-block" style="display: none;"></p>
                                                    </div>
                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                        @endif
                                        @endforeach
                                        <div class="col-md-6 mt-2 mb-2">
                                            <div class="p-2 d-flex justify-content-center"
                                                style="border: 2px dashed #cacaca;border-radius: 10px;">
                                                <button class="button__pilihan pilih" type="button">
                                                    <span class="button__text">Add Tambahan</span>
                                                    <span class="button__icon"><svg class="svg__pilihan"
                                                            fill="none" height="24" stroke="currentColor"
                                                            stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" viewBox="0 0 24 24" width="24"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <line x1="12" x2="12" y1="5"
                                                                y2="19"></line>
                                                            <line x1="5" x2="19" y1="12"
                                                                y2="12"></line>
                                                        </svg></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </form>
    <div class="row">
        <div class="col-12 ">
            <div class="card-plus text-center">
                <div class="row">
                    <div class="col position-relative">
                        <p class="text-uppercase fs-4 fw-bold position-absolute top-50 start-50 translate-middle">Menu
                        </P>
                    </div>
                    <div class="col">
                        <button class="button-primary" id="add_row">
                            + ADD
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 pe-3 text-end ">
            <button type="button" class="button-primary " id="simpan">
                SUBMIT
            </button>
        </div>
    </div>
</div>
