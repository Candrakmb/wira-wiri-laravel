<script>
    var data = function() {
        let valid = true,
            real = '',
            message = '',
            title = '',
            type = '';
        var dt = new Date();
        var time = dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds();
        let scannedContents = [];

        var table = function() {
            swal.fire({
                html: '<h5>Loading...</h5>',
                showConfirmButton: false
            });
            var t = $('#table').DataTable({
                processing: true,
                pageLength: 10,
                serverSide: true,
                searching: true,
                bLengthChange: true,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "Semua"]
                ],
                destroy: true,
                dom: 'Blfrtip',
                buttons: [{
                    extend: 'excel',
                    title: '{{ $title }} - ' + time,
                    text: '<i class="fa fa-file-excel-o"></i> Cetak',
                    titleAttr: 'Cetak',
                    exportOptions: {
                        columns: ':visible',
                        modifier: {
                            page: 'current'
                        }
                    }
                }, ],
                'ajax': {
                    "url": "/menu/table",
                    "method": "POST",
                    "complete": function() {
                        $('.buttons-excel').hide();
                        swal.close();
                    }
                },
                'columns': [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        class: 'text-center',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'user.name',
                        name: 'user.name',
                        class: 'text-left'
                    },
                    {
                        data: 'no_whatsapp',
                        name: 'no_whatsapp',
                        class: 'text-left'
                    },
                    {
                        data: 'alamat',
                        name: 'alamat',
                        class: 'text-left'
                    },
                    {
                        data: 'status_name',
                        name: 'status_name',
                        class: 'text-left',
                    },
                    {
                        data: 'jumlah_menu',
                        name: 'jumlah_menu',
                        class: 'text-left'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        class: 'text-center',
                        orderable: false,
                        searchable: false
                    },

                ],
                "order": [],
                "columnDefs": [{
                    "orderable": false,
                    "targets": [0]
                }],
                "language": {
                    "lengthMenu": "Menampilkan _MENU_ data",
                    "search": "Cari:",
                    "zeroRecords": "Data tidak ditemukan",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    },
                    "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                    "infoEmpty": "Data kosong",
                    "infoFiltered": "(Difilter dari _MAX_ total data)"
                }
            });
            filterKolom(t);
            hideKolom(t);
            cetak(t);

        };


        var filterKolom = function(t) {
            $('.toggle-vis').on('change', function(e) {
                e.preventDefault();
                var column = t.column($(this).attr('data-column'));
                console.log(column);
                column.visible(!column.visible());
            });
        }

        var hideKolom = function(t) {
            var arrKolom = [];
            $('.toggle-vis').each(function(i, value) {
                if (!$(value).is(":checked")) {
                    arrKolom.push(i + 2);
                }
            });
            arrKolom.forEach(function(val) {
                var column = t.column(val);
                column.visible(!column.visible());
            });
        }

        var cetak = function(t) {
            $("#btn-cetak").on("click", function() {
                t.button('.buttons-excel').trigger();
            });
        }

        var setData = function() {
            $('#table_processing').html('Loading...');
            $("select[name='name_id']").val('{{ $data == null ? '' : $data->id_orangtua }}').change();
        }

        var muatUlang = function() {
            $('#btn-muat-ulang').on('click', function() {
                $('#table').DataTable().ajax.reload();
            });
        }
        var no = 0;
        var addRow = function() {
            $('#add_row').on('click', function() {
                var selectedKedai = $('.kedai_id').val();
                var lengthMenu = $('.nama').length;
                if (selectedKedai != ''){
                    @if($type == 'create')
                    no++;
                    @endif
                    @if($type == 'update')
                    let menuItems = @json($menu);
                    menuItems.forEach(item => {
                        if (item.id > no) {
                            no = item.id;
                        }
                    });
                    no++;
                    @endif
                    var html = "";
                    if (no == 0) {
                        $('.card_menu').html("");
                    }
                    html +=`<div class="col-12">
                    <div class="card card-plus my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-3 pb-2">
                        <div class="row">
                            <div class="col-md-6 pt-2">
                                <h6 class="text-white text-capitalize ps-3">Menu ${lengthMenu + 1}</h6>
                            </div>
                            <div class="col-md-6 d-flex justify-content-end pe-4">
                                <button type="button" class="button_trash btn-hapus-detail">
                            <svg viewBox="0 0 448 512" class="svgIcon"><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path></svg>
                            </button>
                            </div>
                        </div>
                        </div>
                    </div>
                    <div class="card-body px-4 pb-3">
                        <div class="row">
                        <div class="col-md-6 mt-2">
                            <div class="col-md-12 mt-5">
                            <div class="d-flex justify-content-center" data-id="${no}">
                                <div class="container_file"> 
                                <div class="header_file img${no}"> 
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> 
                                    <path d="M7 10V9C7 6.23858 9.23858 4 12 4C14.7614 4 17 6.23858 17 9V10C19.2091 10 21 11.7909 21 14C21 15.4806 20.1956 16.8084 19 17.5M7 10C4.79086 10 3 11.7909 3 14C3 15.4806 3.8044 16.8084 5 17.5M7 10C7.43285 10 7.84965 10.0688 8.24006 10.1959M12 12V21M12 12L15 15M12 12L9 15" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg> <p>Browse img to upload!</p>
                                </div> 
                                <input type="file" class="form-control fileImg" name="gambar[]"> 
                                </div>
                            </div>
                        </div>
                        </div>
                        <div class="col-md-6 mt-2">
                            <div class="col-md-12 mt-2">
                            <label for="nama[]" class="label1">Nama Menu</label><span class="required">*</span>
                            <input type="text" id="nama" placeholder="contoh: pecel" name="nama[]"
                                id="nama" class="form-control input nama"
                                 required>
                            <input type="hidden" name="menu_kedai_id[]" value="${no}">
                            <p class="help-block" style="display: none;"></p>
                        </div>
                        <div class="col-md-12 mt-2">
                            <label for="kategori[]" class="label1">kategori</label><span class="required">*</span>
                            <select name="kategori[]" class="form-select select kategori">
                                <option value="">--Select--</option>
                                @if($type == 'create' || $type == 'update')
                                @foreach ($kategori as $item)
                                <option value="{{$item->id}}">{{$item->nama}}</option>
                                @endforeach
                                @endif
                            </select>
                            <p class="help-block" style="display: none;"></p>
                        </div>
                        <div class="col-md-12 mt-2">
                            <label for="harga[]" class="label1">harga</label><span class="required">*</span>
                            <input type="number" id="harga" placeholder="Rp." name="harga[]" class="form-control input" required>
                            <p class="help-block" style="display: none;"></p>
                        </div>
                        <div class="col-md-12 mt-2">
                            <label for="deskripsi[]" class="label1">deskripsi</label><span class="required">*</span>
                            <textarea id="deskripsi" placeholder="contoh: nasi + pecel" name="deskripsi[]" class="form-control input" required></textarea>
                            <p class="help-block" style="display: none;"></p>
                        </div>
                        <div class="col-md-12 mt-2">
                            <label for="status[]" class="label1">Status</label><span class="required">*</span>
                            <select name="status[]" class="form-select select status">
                                <option value="" selected readonly>pilih status</option>
                                <option value="1">Tersedia</option>
                                <option value="0">Kosong</option>
                            </select>
                            <p class="help-block" style="display: none;"></p>
                        </div>
                        </div>
                        <div class="col-md-12 mt-2" data-id="${no}">
                            <div class="row mt-2">
                            <div class="col-md-12 mt-2 mb-2">
                            <div class="p-2 d-flex justify-content-center" style="border: 2px dashed #cacaca;border-radius: 10px;">
                            <button class="button__pilihan pilihKategori" type="button">
                            <span class="button__text">Add Tambahan</span>
                            <span class="button__icon"><svg class="svg__pilihan" fill="none" height="24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><line x1="12" x2="12" y1="5" y2="19"></line><line x1="5" x2="19" y1="12" y2="12"></line></svg></span>
                            </button>
                            </div>
                            </div>
                            </div>
                        </div>
                    </div>
                    </div>
                    </div>
                </div>`;
                    $('.card_menu').append(html);
                    deleteRow(); 
                    addKategori()
                    imgMenu();
                    addPilihan();
                }else{
                    swal.fire({
                        text: 'belum memilih kedai',
                        type: "error",
                        confirmButtonColor: "#EF5350",
                    });
                }
            });
        }

        var deleteRow = function() {
            $('.btn-hapus-detail').unbind().click(function() {
                var cek_menu = $('.nama').length;
                if(cek_menu == 1){
                    swal.fire({
                        text: 'Harus Ada Satu Menu',
                        type: "error",
                        confirmButtonColor: "#EF5350",
                    });
                }else{
                    $(this).parent().parent().parent().parent().parent().remove();
                } 
            });
        }



        var imgMenu = function (){
            $('.fileImg').unbind().change(function(){
                var imgId = $(this).parent().parent().attr('data-id');
                var cardImg = $('.img'+ imgId);
                var file = this.files[0];
                var imgContaine = "";

                if (file && file.type.startsWith('image')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        imgContaine += `<img src="${e.target.result}" alt="">`
                        cardImg.empty().append(imgContaine);
                    };
                    reader.readAsDataURL(file);
                } else {
                    imgContaine += `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> 
                                    <path d="M7 10V9C7 6.23858 9.23858 4 12 4C14.7614 4 17 6.23858 17 9V10C19.2091 10 21 11.7909 21 14C21 15.4806 20.1956 16.8084 19 17.5M7 10C4.79086 10 3 11.7909 3 14C3 15.4806 3.8044 16.8084 5 17.5M7 10C7.43285 10 7.84965 10.0688 8.24006 10.1959M12 12V21M12 12L15 15M12 12L9 15" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg> <p>harus berupa gambar!</p>`
                    cardImg.empty().append(imgContaine);
                }
            })
        }

        var noKategori = 0;
        var addKategori = function (){
            $('.pilihKategori').unbind().click(function() {
                var kedai_id = $(this).parent().parent().parent().parent().attr('data-id');
                @if($type == 'create')
                noKategori++;
                @endif
                @if($type == 'update')
                let kategoriPilih = @json($kategoriMenu);
                kategoriPilih.forEach(key => {
                    if (key.id > noKategori) {
                        noKategori = key.id;
                    }
                });
                noKategori++;
                @endif
                var kategori ="";
                kategori += ` <div class="card card-plus mt-3">
                                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-3 pb-2">
                                    <div class="row">
                                        <div class="col-md-6 pt-2">
                                            <h6 class="text-white text-capitalize ps-3">Menu Opsi</h6>
                                        </div>
                                        <div class="col-md-6 d-flex justify-content-end pe-4">
                                            <button type="button" class="button_trash btn-hapus-kategori" data-id="${kedai_id}">
                                        <svg viewBox="0 0 448 512" class="svgIcon"><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path></svg>
                                        </button>
                                        </div>
                                    </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row justify-content-md-center">
                                        <div class="col-md-6">
                                            <label for="nama_kategori[]" class="label1">Kategori</label><span class="required">*</span>
                                            <input type="hidden" name="menu_id[]" value="${kedai_id}">
                                            <input type="hidden" name="new_id_kategori[]" value="${noKategori}">
                                            <input type="text" id="nama_kategori" placeholder="contoh: rasa" name="nama_kategori[]" class="form-control input nama_kategori" required>
                                            <p class="help-block" style="display: none;"></p>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="opsi[]" class="label1">Opsi</label><span class="required">*</span>
                                            <select name="opsi[]" class="form-select select opsi">
                                                <option value="" selected readonly>pilih Opsi</option>
                                                <option value="1">Wajib</option>
                                                <option value="0">Opsional</option>
                                            </select>
                                            <p class="help-block" style="display: none;"></p>
                                        </div>
                                    </div>
                                    <div class="row mt-3"  data-id="${noKategori}">
                                        <div class="col-md-6 mt-2">
                                            <div class="card card-plus">
                                                <div class="bg-gradient-primary shadow-primary border-radius-lg pt-3 pb-2">
                                                <div class="row">
                                                    <div class="col-md-6 pt-2">
                                                        <h6 class="text-white text-capitalize ps-3">List Opsi</h6>
                                                    </div>
                                                </div>
                                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12 mt-2">
                                            <label for="nama_pilihan[]" class="label1">pilihan</label><span class="required">*</span>
                                            <input type="hidden" name="mark_id_kategori[]" value="${noKategori}">
                                            <input type="text" id="nama_pilihan" placeholder="contoh: pedas" name="nama_pilihan[]" class="form-control input nama_pilihan" required>
                                            <p class="help-block" style="display: none;"></p>
                                        </div>
                                        <div class="col-md-6 mt-2">
                                            <label for="stok_pilihan[]" class="label1">stok</label><span class="required">*</span>
                                            <input type="number" id="stok_pilihan" placeholder="contoh: 1" name="stok_pilihan[]" class="form-control input" required>
                                            <p class="help-block" style="display: none;"></p>
                                        </div>
                                        <div class="col-md-6 mt-2">
                                            <label for="harga_pilihan[]" class="label1">Harga</label><span class="required">*</span>
                                            <input type="text" id="harga_pilihan" placeholder="contoh: 1000" name="harga_pilihan[]" class="form-control input" required>
                                            <p class="help-block" style="display: none;"></p>
                                        </div>
                                        <div class="col-md-12 mt-2">
                                            <label for="status_pilihan[]" class="label1">Status</label><span class="required">*</span>
                                            <select name="status_pilihan[]" class="form-select select status_pilihan">
                                                <option value="" selected readonly>pilih status</option>
                                                <option value="1">Tersedia</option>
                                                <option value="0">Kosong</option>
                                            </select>
                                            <p class="help-block" style="display: none;"></p>
                                        </div>
                                    </div>
                                </div>
                                </div>
                                        </div>
                                        <div class="col-md-6 mt-2">
                                            <div class="p-2 d-flex justify-content-center" style="border: 2px dashed #cacaca;border-radius: 10px;">
                                            <button class="button__pilihan pilih" type="button">
                                            <span class="button__text">Add List Pilihan</span>
                                            <span class="button__icon"><svg class="svg__pilihan" fill="none" height="24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><line x1="12" x2="12" y1="5" y2="19"></line><line x1="5" x2="19" y1="12" y2="12"></line></svg></span>
                                            </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </div>`;
                var buttonAddKategori = "";
                buttonAddKategori += `<div class="col-md-12 mt-2 mb-2">
                                <div class="p-2 d-flex justify-content-center" style="border: 2px dashed #cacaca;border-radius: 10px;">
                                <button class="button__pilihan pilihKategori" type="button">
                                <span class="button__text">Add Tambahan</span>
                                <span class="button__icon"><svg class="svg__pilihan" fill="none" height="24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><line x1="12" x2="12" y1="5" y2="19"></line><line x1="5" x2="19" y1="12" y2="12"></line></svg></span>
                                </button>
                                </div>
                                </div>`;
                $(this).parent().parent().after(buttonAddKategori);
                $(this).parent().parent().empty().append(kategori);
                deleteKategori();
                addKategori();
                addPilihan();
            })
        }

        
        var addPilihan = function() {
            $('.pilih').unbind().click(function() {
                var pilihNo = $(this).parent().parent().parent().attr('data-id');
                var data = "";
                data +=`
                                <div class="card card-plus">
                                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-3 pb-2">
                                    <div class="row">
                                        <div class="col-md-6 pt-2">
                                            <h6 class="text-white text-capitalize ps-3">List Opsi</h6>
                                        </div>
                                        <div class="col-md-6 d-flex justify-content-end pe-4">
                                            <button type="button" class="button_trash btn-hapus-pilihan" data-id="${pilihNo}">
                                        <svg viewBox="0 0 448 512" class="svgIcon"><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path></svg>
                                        </button>
                                        </div>
                                    </div>
                                    </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12 mt-2">
                                            <label for="nama_pilihan[]" class="label1">pilihan</label><span class="required">*</span>
                                            <input type="hidden" name="mark_id_kategori[]" value="${pilihNo}">
                                            <input type="text" id="nama_pilihan" placeholder="contoh: pedas" name="nama_pilihan[]" class="form-control input nama_pilihan" required>
                                            <p class="help-block" style="display: none;"></p>
                                        </div>
                                        <div class="col-md-6 mt-2">
                                            <label for="stok_pilihan[]" class="label1">stok</label><span class="required">*</span>
                                            <input type="number" id="stok_pilihan" placeholder="contoh: 1" name="stok_pilihan[]" class="form-control input" required>
                                            <p class="help-block" style="display: none;"></p>
                                        </div>
                                        <div class="col-md-6 mt-2">
                                            <label for="harga_pilihan[]" class="label1">Harga</label><span class="required">*</span>
                                            <input type="text" id="harga_pilihan" placeholder="contoh: 1000" name="harga_pilihan[]" class="form-control input" required>
                                            <p class="help-block" style="display: none;"></p>
                                        </div>
                                        <div class="col-md-12 mt-2">
                                            <label for="status_pilihan[]" class="label1">Status</label><span class="required">*</span>
                                            <select name="status_pilihan[]" class="form-select select status_pilihan">
                                                <option value="" selected readonly>pilih status</option>
                                                <option value="1">Tersedia</option>
                                                <option value="0">Kosong</option>
                                            </select>
                                            <p class="help-block" style="display: none;"></p>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            
                        `;
                var buttonAdd = "";
                buttonAdd += `<div class="col-md-6 mt-2 mb-2">
                                <div class="p-2 d-flex justify-content-center" style="border: 2px dashed #cacaca;border-radius: 10px;">
                                <button class="button__pilihan pilih" type="button">
                                <span class="button__text">Add List Pilihan</span>
                                <span class="button__icon"><svg class="svg__pilihan" fill="none" height="24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><line x1="12" x2="12" y1="5" y2="19"></line><line x1="5" x2="19" y1="12" y2="12"></line></svg></span>
                                </button>
                                </div>
                                </div>`;
                $(this).parent().parent().after(buttonAdd);
                $(this).parent().parent().empty().append(data);
                deletepilihan();
                addPilihan();
            });
        };
        var deleteKategori = function() {
            $('.btn-hapus-kategori').unbind().click(function() {
                $(this).parent().parent().parent().parent().parent().remove();
                var id= $(this).data('id');
            });
        }
        var deletepilihan = function() {
            $('.btn-hapus-pilihan').unbind().click(function() {
                $(this).parent().parent().parent().parent().parent().remove();
                var id= $(this).data('id');
            });
        }

       
        var create = function() {
            $('#simpan').click(function(e) {
                e.preventDefault();
                swal.fire({
                        title: 'Apakah Anda Yakin?',
                        text: 'Menyimpan Data Ini',
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#2196F3',
                        confirmButtonText: 'Ya',
                        cancelButtonText: 'Tidak'
                    })
                    .then((result) => {
                        if (result.value) {
                            var formdata = $(this).serialize();
                            valid = true
                            var err = 0;
                            $('.help-block').hide();
                            $('.form-error').removeClass('form-error');
                            $('#form-data').find('input, select').each(function() {
                                if ($(this).prop('required')) {
                                    if (err == 0) {
                                        if ($(this).val() == "") {
                                            valid = false;
                                            real = this.name.replace(/\[\]/g, '');
                                            title = $('label[for="' + this.name + '"]')
                                                .html();
                                            type = '';
                                            if ($(this).is("input")) {
                                                type = 'diisi';
                                            } else {
                                                type = 'dipilih';
                                            }
                                            err++;
                                        }
                                    }
                                }
                            })
                            if (!valid) {
                                if (type == 'diisi') {
                                    $("input[name=" + real + "]").addClass('form-error');
                                    $($("input[name=" + real + "]").closest('div').find(
                                        '.help-block')).html(title + 'belum ' + type);
                                    $($("input[name=" + real + "]").closest('div').find(
                                        '.help-block')).show();
                                } else {
                                    $("select[name=" + real + "]").closest('div').find(
                                        '.select2-selection--single').addClass('form-error');
                                    $($("select[name=" + real + "]").closest('div').find(
                                        '.help-block')).html(title + 'belum ' + type);
                                    $($("select[name=" + real + "]").closest('div').find(
                                        '.help-block')).show();
                                }

                                swal.fire({
                                    text: title + 'belum ' + type,
                                    type: "error",
                                    confirmButtonColor: "#EF5350",
                                });
                            } else {
                                var formData = new FormData($('#form-data')[0]);
                                $.ajax({
                                    @if ($type == 'create')
                                        url: "/menu/createform",
                                    @else
                                        url: "/menu/updateform",
                                    @endif
                                    type: "POST",
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                    beforeSend: function() {
                                        swal.fire({
                                            html: '<h5>Loading...</h5>',
                                            showConfirmButton: false
                                        });
                                    },
                                    success: function(result) {
                                        if (result.type == 'success') {
                                            swal.fire({
                                                title: result.title,
                                                text: result.text,
                                                confirmButtonColor: result
                                                    .ButtonColor,
                                                type: result.type,
                                            }).then((result) => {
                                                location.href = "/menu";
                                            });
                                        } else {
                                            swal.fire({
                                                title: result.title,
                                                text: result.text,
                                                confirmButtonColor: result
                                                    .ButtonColor,
                                                type: result.type,
                                            });
                                        }
                                    }
                                });
                            }
                        } else {
                            swal.fire({
                                text: 'Aksi Dibatalkan!',
                                type: "info",
                                confirmButtonColor: "#EF5350",
                            });
                        }
                    });
            });
        }

        var hapus = function() {
            $('#table').on('click', '#btn-hapus', function() {
                var baris = $(this).parents('tr')[0];
                var table = $('#table').DataTable();
                var data = table.row(baris).data();

                swal.fire({
                        title: 'Apakah Anda Yakin?',
                        text: 'Menghapus semua data menu di restaurant ini',
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#2196F3',
                        confirmButtonText: 'Ya',
                        cancelButtonText: 'Tidak'
                    })
                    .then((result) => {
                        if (result.value) {
                            var fd = new FormData();
                            fd.append('_token', '{{ csrf_token() }}');
                            fd.append('id', data.id);

                            $.ajax({
                                url: "/menu/deleteform",
                                type: "POST",
                                data: fd,
                                dataType: "json",
                                contentType: false,
                                processData: false,
                                beforeSend: function() {
                                    swal.fire({
                                        html: '<h5>Loading...</h5>',
                                        showConfirmButton: false
                                    });
                                },
                                success: function(result) {
                                    swal.fire({
                                        title: result.title,
                                        text: result.text,
                                        confirmButtonColor: result.ButtonColor,
                                        type: result.type,
                                    });

                                    if (result.type == 'success') {
                                        swal.fire({
                                            title: result.title,
                                            text: result.text,
                                            confirmButtonColor: result
                                                .ButtonColor,
                                            type: result.type,
                                        }).then((result) => {
                                            $('#table').DataTable().ajax
                                            .reload();
                                        });
                                    } else {
                                        swal.fire({
                                            title: result.title,
                                            text: result.text,
                                            confirmButtonColor: result
                                                .ButtonColor,
                                            type: result.type,
                                        });
                                    }
                                }
                            });
                        } else {
                            swal.fire({
                                text: 'Aksi Dibatalkan!',
                                type: "info",
                                confirmButtonColor: "#EF5350",
                            });
                        }
                    });
            });
        }
        @if($type == 'index')
        var map = function (){
            var map = L.map('map').setView([-7.152186, 111.883674],15);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            const LeafIcon = L.Icon.extend({
                options: {
                    iconSize: [50, 70], // Sesuaikan dengan ukuran yang kamu inginkan
                    iconAnchor: [25, 70], // Sesuaikan dengan posisi anchor yang tepat
                    popupAnchor: [0, -35] // Jika ingin menampilkan popup
                }
            });

            const greenIcon = new LeafIcon({iconUrl: '/assets/icon_maps/restaurant.png'});
            
            let kedaiMaps = @json($kedai_maps);
            kedaiMaps.forEach(item => {
                const statusBadge = item.status == '1' ? '<div class="badge rounded-pill bg-success">Buka</div>' : '<div class="badge rounded-pill bg-danger">Tutup</div>';

                // Menggunakan backticks (`) untuk memungkinkan multiline strings dan interpolation
                const popupContent = `<h5>Kedai ${item.user.name}</h5><br>${statusBadge}`;

                L.marker([item.latitude, item.longitude], {icon: greenIcon}).addTo(map).bindPopup(popupContent);
            });
        }
        @endif

        return {
            init: function() {
                @if ($type == 'index')
                    table();
                    muatUlang();
                    map();
                @endif
                @if ($type == 'create' || $type == 'update')
                addRow();
                addPilihan();
                addKategori();
                imgMenu();
                deleteRow();
                deletepilihan();
                deleteKategori()
                @endif
                setData();
                create();
                hapus();
                
            }
        }
    }();
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.fn.dataTable.ext.errMode = 'none';
        data.init();
        
    });
</script>
