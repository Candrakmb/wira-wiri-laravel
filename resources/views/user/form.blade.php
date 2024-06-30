<div class="container-fluid py-4">
  <form id="form-data" method="post" autocompleted="off" enctype="multipart/form-data">
    @csrf
    @if($type == 'create')
    <div class="row mb-3">
      <div class="col-12">
        <div class="card-plus p-3 d-flex justify-content-center">
        <div class="wrapper-radio z-index-2">
          <div class="option-radio">
            <input class="input-radio role" type="radio" name="role" value="pelanggan" checked="">
            <div class="btn-radio">
              <span class="span-radio">Pelanggan</span>
            </div>
          </div>
          <div class="option-radio">
            <input class="input-radio role" type="radio" name="role" value="driver">
            <div class="btn-radio">
              <span class="span-radio">Driver</span>
            </div>  </div>
          <div class="option-radio">
            <input class="input-radio role" type="radio" name="role" value="kedai">
            <div class="btn-radio">
              <span class="span-radio">Kedai</span>
            </div>  
          </div>
        </div>
      </div>
    </div>
  </div>

    <div class="row card_input">
      <div class="col-12">
        <div class="card card-plus my-4">
          <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
              <h6 class="text-white text-capitalize ps-3">User pelanggan</h6>
            </div>
          </div>
          <div class="card-body px-4 pb-3">
            <div class="row ">
              <div class="col-md-6 mt-2">
                <label for="name[]" class="label1">Nama</label><span class="required">*</span>
                <input type="text" placeholder="Silahkan Masukkan" name="name[]"
                    id="name" class="form-control input name" required>
                <p class="help-block" style="display: none;"></p>
              </div>
              <div class="col-md-6 mt-2">
                <label for="email[]" class="label1">Email</label><span class="required">*</span>
                <input type="text" id="email" placeholder="Silahkan Masukkan" name="email[]" class="form-control input" required>
                <p class="help-block" style="display: none;"></p>
                </div>
                <div class="col-md-6 mt-2">
                <label for="password[]" class="label1">Password</label><span class="required">*</span>
                <input type="text" id="password" placeholder="Silahkan Masukkan" name="password[]" class="form-control input" value="usertest" required>
                <p class="help-block" style="display: none;"></p>
                </div>
                <div class="col-md-6 mt-2">
                <label for="no_wa[]" class="label1">No Whatsapp</label><span class="required">*</span>
                <input type="text" id="no_wa" placeholder="Silahkan Masukkan" name="no_wa[]" class="form-control input" required>
                <p class="help-block" style="display: none;"></p>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif
    @if ($type == 'update')
    <div class="row card_input">
      <div class="col-12">
        <div class="card card-plus my-4">
          <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
              <h6 class="text-white text-uppercase ps-3">{{$dataUser->name}}</h6>
            </div>
          </div>
          <div class="card-body px-4 pb-3">
            <div class="row ">
              @if ($driver != null)
                <div class="col-md-12 mt-5">
                    <div class="d-flex justify-content-center" data-id="{{ $driver->id }}">
                        <div class="container_file">
                            <div class="header_file img{{ $driver->id }}">
                              <img src="{{asset('storage/image/driver/'.$driver->img_profil)}}" alt="{{$driver->nama}}">
                            </div>
                            <input type="file" class="form-control fileImg" name="profil">
                        </div>
                    </div>
                </div>
              @endif
              @if ($kedai != null)
              <div class="col-md-12 mt-5">
                  <div class="d-flex justify-content-center" data-id="{{ $kedai->id }}">
                      <div class="container_file">
                          <div class="header_file img{{ $kedai->id }}">
                            <img src="{{asset('storage/image/kedai/'.$kedai->img)}}" alt="{{$kedai->nama}}">
                          </div>
                          <input type="file" class="form-control fileImg" name="profil">
                      </div>
                  </div>
              </div>
            @endif
              <div class="col-md-6 mt-2">
                <label for="name" class="label1">Nama</label><span class="required">*</span>
                <input type="text" placeholder="Silahkan Masukkan" name="name"
                    id="name" class="form-control input name" value="{{$dataUser->name}}" required>
                <input type="hidden" name="id_user" class="form-control input" value="{{$dataUser->id}}" required>
                @if ($pelanggan != null)
                <input type="hidden" name="id" class="form-control input" value="{{$pelangan->id}}">
                <input type="hidden" name="role" class="form-control input" value="pelanggan">
                @endif
                @if ($driver != null)
                <input type="hidden" name="id" class="form-control input" value="{{$driver->id}}">
                <input type="hidden" name="role" class="form-control input" value="driver">
                @endif
                @if ($kedai != null)
                <input type="hidden" name="id" class="form-control input" value="{{$kedai->id}}">
                <input type="hidden" name="role" class="form-control input" value="kedai">
                @endif
                <p class="help-block" style="display: none;"></p>
              </div>
              <div class="col-md-6 mt-2">
                <label for="email" class="label1">Email</label><span class="required">*</span>
                <input type="text" id="email" placeholder="Silahkan Masukkan" name="email" class="form-control input" value="{{$dataUser->email}}" required>
                <p class="help-block" style="display: none;"></p>
              </div>
              <div class="col-md-6 mt-2">
                <label for="password" class="label1">Password</label><span class="required">*</span>
                <input type="text" id="password" placeholder="Silahkan Masukkan" name="password" class="form-control input">
                <p class="help-block" style="display: none;"></p>
              </div>
              @if ($pelanggan != null)
              <div class="col-md-6 mt-2">
                <label for="no_wa" class="label1">No Whatsapp</label><span class="required">*</span>
                <input type="text" id="no_wa" placeholder="Silahkan Masukkan" name="no_wa" class="form-control input" value="{{$pelanggan->no_whatsapp}}" required>
                <p class="help-block" style="display: none;"></p>
              </div>
              @endif
              @if ($driver != null)
              <div class="col-md-6 mt-2">
                <label for="no_wa" class="label1">No Whatsapp</label><span class="required">*</span>
                <input type="text" id="no_wa" placeholder="Silahkan Masukkan" name="no_wa" class="form-control input" value="{{$driver->no_whatsapp}}" required>
                <p class="help-block" style="display: none;"></p>
              </div>
              <div class="col-md-6 mt-2">
                <label for="tanggal_lahir" class="label1">tanggal_lahir</label><span class="required">*</span>
                <input type="date" id="tanggal_lahir" placeholder="Silahkan Masukkan" name="tanggal_lahir" class="form-control input" value="{{$driver->tanggal_lahir}}">
                <p class="help-block" style="display: none;"></p>
              </div>
              <div class="col-md-6 mt-2">
                <label for="alamat" class="label1">Alamat</label><span class="required">*</span>
                <input type="text" id="alamat" placeholder="Silahkan Masukkan" name="alamat" class="form-control input" value="{{$driver->alamat}}">
                <p class="help-block" style="display: none;"></p>
              </div>
              <div class="col-md-6 mt-2">
                <label for="no_plat" class="label1">Plat Nomer</label><span class="required">*</span>
                <input type="text" id="no_plat" placeholder="Silahkan Masukkan" name="no_plat" class="form-control input" value="{{$driver->no_plat}}" required>
                <p class="help-block" style="display: none;"></p>
                </div>
              <div class="col-md-6 mt-2">
              <label for="jenis_kelamin" class="label1">Jenis Kelamin</label><span class="required">*</span>
              <select name="jenis_kelamin" id="jenis_kelamin" class="form-select select jenis_kelamin">
                <option value="" readonly>pilih....</option>
                <option value="L" {{ $driver->jenis_kelamin == 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ $driver->jenis_kelamin == 'P' ? 'selected' : '' }}>Perempuan</option>                
              </select>
              </div>
              @endif
              @if ($kedai != null)
              <div class="col-md-6 mt-2">
                <label for="no_wa" class="label1">No Whatsapp</label><span class="required">*</span>
                <input type="test" id="no_wa" placeholder="Silahkan Masukkan" name="no_wa" class="form-control input" value="{{$kedai->no_whatsapp}}" required>
                <p class="help-block" style="display: none;"></p>
              </div>
              <div class="col-md-6 mt-2">
                <label for="alamat" class="label1">Alamat</label><span class="required">*</span>
                <input type="text" id="alamat" placeholder="Silahkan Masukkan" name="alamat" class="form-control input" value="{{$kedai->alamat}}">
                <p class="help-block" style="display: none;"></p>
              </div>
              <div class="col-md-6 mt-2">
                <label for="latitude" class="label1">Latitude</label><span class="required">*</span>
                <input type="number" id="latitude" placeholder="Silahkan Masukkan" name="latitude" class="form-control input" value="{{$kedai->latitude}}" required>
                <p class="help-block" style="display: none;"></p>
              </div>
              <div class="col-md-6 mt-2">
                <label for="longitude" class="label1">Longitude</label><span class="required">*</span>
                <input type="number" id="longitude" placeholder="Silahkan Masukkan" name="longitude" class="form-control input" value="{{$kedai->longitude}}" required>
                <p class="help-block" style="display: none;"></p>
              </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif
      <div class="row">
        <div class="col-12 ">
          <div class="card-plus text-center">
            <div class="row">
              <div class="col" >
                <button type="button" class="button-primary" id="simpan" > 
                SUBMIT
              </button>
            </div>
          </form>
              @if($type == 'create')
              <div class="col"> 
                <button type="button" class="button-primary" id="add_row"> 
                + ADD
              </button>
              @endif
              </div>
            </div>
          </div>
        </div>
      </div>                           
</div>