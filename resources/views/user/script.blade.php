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
                    "url": "/user/table",
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
                        data: 'name',
                        name: 'name',
                        class: 'text-left'
                    },
                    {
                        data: 'email',
                        name: 'email',
                        class: 'text-left'
                    },
                    {
                        data: 'role',
                        name: 'role',
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
                var selectedRole = $('.role:checked').val();
                no++;
                var html = "";
                if (no == 0) {
                    $('.card_input').html("");
                }
                if (selectedRole == 'driver') {
                    html += `
                    <div class="col-12">
                    <div class="card card-plus my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-3 pb-2">
                        <div class="row">
                            <div class="col-md-6 pt-2">
                                <h6 class="text-white text-capitalize ps-3">User ${selectedRole}</h6>
                            </div>
                            <div class="col-md-6 d-flex justify-content-end pe-4">
                                <button class="button_trash btn-hapus-detail">
                            <svg viewBox="0 0 448 512" class="svgIcon"><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path></svg>
                            </button>
                            </div>
                        </div>
                        </div>
                    </div>
                    <div class="card-body px-4 pb-3">
                        <div class="row">
                            <div class="col-md-12 mb-1">
                            <div class="d-flex justify-content-center" data-id="${no}">
                                <div class="container_file"> 
                                <div class="header_file img${no}"> 
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> 
                                    <path d="M7 10V9C7 6.23858 9.23858 4 12 4C14.7614 4 17 6.23858 17 9V10C19.2091 10 21 11.7909 21 14C21 15.4806 20.1956 16.8084 19 17.5M7 10C4.79086 10 3 11.7909 3 14C3 15.4806 3.8044 16.8084 5 17.5M7 10C7.43285 10 7.84965 10.0688 8.24006 10.1959M12 12V21M12 12L15 15M12 12L9 15" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg> <p>Browse img to upload profil!</p>
                                </div> 
                                <input type="file" class="form-control fileImg" name="profil[]"> 
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mt-2">
                            <label for="name[]" class="label1">Nama</label><span class="required">*</span>
                            <input type="text" id="name" placeholder="Silahkan Masukkan" name="name[]"
                                id="name" class="form-control input name"
                                 required>
                            <p class="help-block" style="display: none;"></p>
                        </div>
                        <div class="col-md-6 mt-2">
                                <label for="email" class="label1">Email</label><span class="required">*</span>
                                <input type="text" id="email" placeholder="Silahkan Masukkan" name="email[]" class="form-control input" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                                <div class="col-md-6 mt-2">
                                <label for="password" class="label1">Password</label><span class="required">*</span>
                                <input type="text" id="password" placeholder="Silahkan Masukkan" name="password[]" class="form-control input" value="driverwirawiri" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                                <div class="col-md-6 mt-2">
                                <label for="no_wa" class="label1">No Whatsapp</label><span class="required">*</span>
                                <input type="number" id="no_wa" placeholder="Silahkan Masukkan" name="no_wa[]" class="form-control input" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                                <div class="col-md-6 mt-2">
                                <label for="tgl_lhr" class="label1">tanggal Lahir</label><span class="required">*</span>
                                <input type="date" id="tgl_lhr" placeholder="Silahkan Masukkan" name="tgl_lhr[]" class="form-control input" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                                <div class="col-md-6 mt-2">
                                <label for="alamat" class="label1">Alamat</label><span class="required">*</span>
                                <input type="text" id="alamat" placeholder="Silahkan Masukkan" name="alamat[]" class="form-control input" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                                <div class="col-md-6 mt-2">
                                <label for="no_plat" class="label1">Plat Nomer</label><span class="required">*</span>
                                <input type="text" id="no_plat" placeholder="Silahkan Masukkan" name="no_plat[]" class="form-control input" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                                <div class="col-md-6 mt-2">
                                <label for="jenis_kelamin" class="label1">Jenis Kelamin</label><span class="required">*</span>
                                <select name="jenis_kelamin[]" id="jenis_kelamin" class="form-select select jenis_kelamin">
                                <option selected readonly>pilih....</option>
                                <option value="L" >Laki laki</option>
                                <option value="P">Perempuan</option>
                                </select>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                        </div>
                    </div>
                    </div>
                </div>`;
                } else if (selectedRole == 'pelanggan') {
                    html += `<div class="col-12">
                    <div class="card card-plus my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-3 pb-2">
                        <div class="row">
                            <div class="col-md-6 pt-2">
                                <h6 class="text-white text-capitalize ps-3">User ${selectedRole}</h6>
                            </div>
                            <div class="col-md-6 d-flex justify-content-end pe-4">
                                <button class="button_trash btn-hapus-detail">
                            <svg viewBox="0 0 448 512" class="svgIcon"><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path></svg>
                            </button>
                            </div>
                        </div>
                        </div>
                    </div>
                    <div class="card-body px-4 pb-3">
                        <div class="row">
                        <div class="col-md-6 mt-2">
                            <label for="name[]" class="label1">Nama</label><span class="required">*</span>
                            <input type="text" id="name" placeholder="Silahkan Masukkan" name="name[]"
                                id="name" class="form-control input name"
                                 required>
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
                                <input type="number" id="no_wa" placeholder="Silahkan Masukkan" name="no_wa[]" class="form-control input" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                        </div>
                    </div>
                    </div>
                </div>`;
                } else if (selectedRole == 'kedai') {
                    html += `<div class="col-12">
                    <div class="card card-plus my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-3 pb-2">
                        <div class="row">
                            <div class="col-md-6 pt-2">
                                <h6 class="text-white text-capitalize ps-3">User ${selectedRole}</h6>
                            </div>
                            <div class="col-md-6 d-flex justify-content-end pe-4">
                                <button class="button_trash btn-hapus-detail">
                            <svg viewBox="0 0 448 512" class="svgIcon"><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path></svg>
                            </button>
                            </div>
                        </div>
                        </div>
                    </div>
                    <div class="card-body px-4 pb-3">
                        <div class="row">
                            <div class="col-md-12 mb-1">
                            <div class="d-flex justify-content-center" data-id="${no}">
                                <div class="container_file"> 
                                <div class="header_file img${no}"> 
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> 
                                    <path d="M7 10V9C7 6.23858 9.23858 4 12 4C14.7614 4 17 6.23858 17 9V10C19.2091 10 21 11.7909 21 14C21 15.4806 20.1956 16.8084 19 17.5M7 10C4.79086 10 3 11.7909 3 14C3 15.4806 3.8044 16.8084 5 17.5M7 10C7.43285 10 7.84965 10.0688 8.24006 10.1959M12 12V21M12 12L15 15M12 12L9 15" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg> <p>Browse img to upload profil!</p>
                                </div> 
                                <input type="file" class="form-control fileImg" name="profil[]"> 
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mt-2">
                            <label for="name[]" class="label1">Nama</label><span class="required">*</span>
                            <input type="text" id="name" placeholder="Silahkan Masukkan" name="name[]"
                                id="name" class="form-control input"
                                 required>
                            <p class="help-block" style="display: none;"></p>
                        </div>
                        <div class="col-md-6 mt-2">
                                <label for="email[]" class="label1">Email</label><span class="required">*</span>
                                <input type="text" id="email" placeholder="Silahkan Masukkan" name="email[]" class="form-control input name" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                                <div class="col-md-6 mt-2">
                                <label for="password[]" class="label1">Password</label><span class="required">*</span>
                                <input type="text" id="password" placeholder="Silahkan Masukkan" name="password[]" class="form-control input" value="kedaiwirawiri" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                                <div class="col-md-6 mt-2">
                                <label for="no_wa[]" class="label1">No Whatsapp</label><span class="required">*</span>
                                <input type="number" id="no_wa" placeholder="Silahkan Masukkan" name="no_wa[]" class="form-control input" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                                <div class="col-md-6 mt-2">
                                <label for="alamat[]" class="label1">Alamat</label><span class="required">*</span>
                                <input type="text" id="alamat" placeholder="Silahkan Masukkan" name="alamat[]" class="form-control input" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                                <div class="col-md-6 mt-2">
                                <label for="latitude[]" class="label1">Latitude</label><span class="required">*</span>
                                <input type="text" id="latitude" placeholder="Silahkan Masukkan" name="latitude[]" class="form-control input" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                                <div class="col-md-6 mt-2">
                                <label for="longitud[]" class="label1">Longitud</label><span class="required">*</span>
                                <input type="text" id="longitud" placeholder="Silahkan Masukkan" name="longitud[]" class="form-control input" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                        </div>
                    </div>
                    </div>
                </div>`;
                }
                $('.card_input').append(html);
                deleteRow();
                addData();
                imgMenu();
            });
        }

        var deleteRow = function() {
            $('.btn-hapus-detail').unbind().click(function() {
                $(this).parent().parent().parent().parent().parent().remove();
            });
        }

        var addData = function() {
            $('.role').unbind().change(function() {
                var selectedRole = $(this).val();
               
                var lengthUser = $('.name').length;
                console.log(selectedRole,lengthUser);
                var data = "";
                for (var i = 0; i < lengthUser; i++) {
                    if (selectedRole == 'driver') {
                    data += `
                    <div class="col-12">
                    <div class="card card-plus my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-3 pb-2">
                        <div class="row">
                            <div class="col-md-6 pt-2">
                                <h6 class="text-white text-capitalize ps-3">User ${selectedRole}</h6>
                            </div>
                            <div class="col-md-6 d-flex justify-content-end pe-4">
                                <button class="button_trash btn-hapus-detail">
                            <svg viewBox="0 0 448 512" class="svgIcon"><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path></svg>
                            </button>
                            </div>
                        </div>
                        </div>
                    </div>
                    <div class="card-body px-4 pb-3">
                        <div class="row">
                            <div class="col-md-12  mb-1">
                            <div class="d-flex justify-content-center" data-id="${no}">
                                <div class="container_file"> 
                                <div class="header_file img${no}"> 
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> 
                                    <path d="M7 10V9C7 6.23858 9.23858 4 12 4C14.7614 4 17 6.23858 17 9V10C19.2091 10 21 11.7909 21 14C21 15.4806 20.1956 16.8084 19 17.5M7 10C4.79086 10 3 11.7909 3 14C3 15.4806 3.8044 16.8084 5 17.5M7 10C7.43285 10 7.84965 10.0688 8.24006 10.1959M12 12V21M12 12L15 15M12 12L9 15" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg> <p>Browse img to upload profil!</p>
                                </div> 
                                <input type="file" class="form-control fileImg" name="profil[]"> 
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mt-2">
                            <label for="name[]" class="label1">Nama</label><span class="required">*</span>
                            <input type="text" id="name" placeholder="Silahkan Masukkan" name="name[]"
                                id="name" class="form-control input name"
                                 required>
                            <p class="help-block" style="display: none;"></p>
                        </div>
                        <div class="col-md-6 mt-2">
                                <label for="email" class="label1">Email</label><span class="required">*</span>
                                <input type="text" id="email" placeholder="Silahkan Masukkan" name="email[]" class="form-control input" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                                <div class="col-md-6 mt-2">
                                <label for="password" class="label1">Password</label><span class="required">*</span>
                                <input type="text" id="password" placeholder="Silahkan Masukkan" name="password[]" class="form-control input" value="driverwirawiri" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                                <div class="col-md-6 mt-2">
                                <label for="no_wa" class="label1">No Whatsapp</label><span class="required">*</span>
                                <input type="number" id="no_wa" placeholder="Silahkan Masukkan" name="no_wa[]" class="form-control input" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                                <div class="col-md-6 mt-2">
                                <label for="tgl_lhr" class="label1">tanggal Lahir</label><span class="required">*</span>
                                <input type="date" id="tgl_lhr" placeholder="Silahkan Masukkan" name="tgl_lhr[]" class="form-control input" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                                <div class="col-md-6 mt-2">
                                <label for="alamat" class="label1">Alamat</label><span class="required">*</span>
                                <input type="text" id="alamat" placeholder="Silahkan Masukkan" name="alamat[]" class="form-control input" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                                <div class="col-md-6 mt-2">
                                <label for="no_plat" class="label1">Plat Nomer</label><span class="required">*</span>
                                <input type="text" id="no_plat" placeholder="Silahkan Masukkan" name="no_plat[]" class="form-control input" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                                <div class="col-md-6 mt-2">
                                <label for="jenis_kelamin" class="label1">Jenis Kelamin</label><span class="required">*</span>
                                <select name="jenis_kelamin[]" id="jenis_kelamin" class="form-select select jenis_kelamin">
                                <option selected readonly>pilih....</option>
                                <option value="L" >Laki laki</option>
                                <option value="P">Perempuan</option>
                                </select>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                        </div>
                    </div>
                    </div>
                </div>`;
                } else if (selectedRole == 'pelanggan') {
                    data += `<div class="col-12">
                    <div class="card card-plus my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-3 pb-2">
                        <div class="row">
                            <div class="col-md-6 pt-2">
                                <h6 class="text-white text-capitalize ps-3">User ${selectedRole}</h6>
                            </div>
                            <div class="col-md-6 d-flex justify-content-end pe-4">
                                <button class="button_trash btn-hapus-detail">
                            <svg viewBox="0 0 448 512" class="svgIcon"><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path></svg>
                            </button>
                            </div>
                        </div>
                        </div>
                    </div>
                    <div class="card-body px-4 pb-3">
                        <div class="row">
                        <div class="col-md-6 mt-2">
                            <label for="name[]" class="label1">Nama</label><span class="required">*</span>
                            <input type="text" id="name" placeholder="Silahkan Masukkan" name="name[]"
                                id="name" class="form-control input name"
                                 required>
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
                                <input type="number" id="no_wa" placeholder="Silahkan Masukkan" name="no_wa[]" class="form-control input" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                        </div>
                    </div>
                    </div>
                </div>`;
                } else if (selectedRole == 'kedai') {
                    data += `<div class="col-12">
                    <div class="card card-plus my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-3 pb-2">
                        <div class="row">
                            <div class="col-md-6 pt-2">
                                <h6 class="text-white text-capitalize ps-3">User ${selectedRole}</h6>
                            </div>
                            <div class="col-md-6 d-flex justify-content-end pe-4">
                                <button class="button_trash btn-hapus-detail">
                            <svg viewBox="0 0 448 512" class="svgIcon"><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path></svg>
                            </button>
                            </div>
                        </div>
                        </div>
                    </div>
                    <div class="card-body px-4 pb-3">
                        <div class="row">
                            <div class="col-md-12 mb-1">
                            <div class="d-flex justify-content-center" data-id="${no}">
                                <div class="container_file"> 
                                <div class="header_file img${no}"> 
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> 
                                    <path d="M7 10V9C7 6.23858 9.23858 4 12 4C14.7614 4 17 6.23858 17 9V10C19.2091 10 21 11.7909 21 14C21 15.4806 20.1956 16.8084 19 17.5M7 10C4.79086 10 3 11.7909 3 14C3 15.4806 3.8044 16.8084 5 17.5M7 10C7.43285 10 7.84965 10.0688 8.24006 10.1959M12 12V21M12 12L15 15M12 12L9 15" stroke="#000000" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg> <p>Browse img to upload profil!</p>
                                </div> 
                                <input type="file" class="form-control fileImg" name="profil[]"> 
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mt-2">
                            <label for="name[]" class="label1">Nama</label><span class="required">*</span>
                            <input type="text" id="name" placeholder="Silahkan Masukkan" name="name[]"
                                id="name" class="form-control input"
                                 required>
                            <p class="help-block" style="display: none;"></p>
                        </div>
                        <div class="col-md-6 mt-2">
                                <label for="email[]" class="label1">Email</label><span class="required">*</span>
                                <input type="text" id="email" placeholder="Silahkan Masukkan" name="email[]" class="form-control input name" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                                <div class="col-md-6 mt-2">
                                <label for="password[]" class="label1">Password</label><span class="required">*</span>
                                <input type="text" id="password" placeholder="Silahkan Masukkan" name="password[]" class="form-control input" value="kedaiwirawiri" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                                <div class="col-md-6 mt-2">
                                <label for="no_wa[]" class="label1">No Whatsapp</label><span class="required">*</span>
                                <input type="number" id="no_wa" placeholder="Silahkan Masukkan" name="no_wa[]" class="form-control input" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                                <div class="col-md-6 mt-2">
                                <label for="alamat[]" class="label1">Alamat</label><span class="required">*</span>
                                <input type="text" id="alamat" placeholder="Silahkan Masukkan" name="alamat[]" class="form-control input" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                                <div class="col-md-6 mt-2">
                                <label for="latitude[]" class="label1">Latitude</label><span class="required">*</span>
                                <input type="text" id="latitude" placeholder="Silahkan Masukkan" name="latitude[]" class="form-control input" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                                <div class="col-md-6 mt-2">
                                <label for="longitud[]" class="label1">Longitud</label><span class="required">*</span>
                                <input type="text" id="longitud" placeholder="Silahkan Masukkan" name="longitud[]" class="form-control input" required>
                                <p class="help-block" style="display: none;"></p>
                                </div>
                        </div>
                    </div>
                    </div>
                </div>`;
                }
                // Mengosongkan dan mengisi kembali konten div add_data
                $('.card_input').empty().append(data);
                imgMenu();
                } 
            });
        };

        var imgMenu = function (){
            $('.fileImg').unbind().change(function(){
                var imgId = $(this).parent().parent().attr('data-id');
                var cardImg = $('.img'+ imgId);
                console.log(cardImg);
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
                                        url: "/user/createform",
                                    @else
                                        url: "/user/updateform",
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
                                                location.href = "/user";
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
                        text: 'Menghapus Data Ini',
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
                                url: "/user/deleteform",
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
        @if ($type == 'index')
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
            let driverMaps = @json($driver_maps);
            driverMaps.forEach(item => {
                const statusBadge = item.status == '1' ? '<div class="badge rounded-pill bg-success">ON</div>' : '<div class="badge rounded-pill bg-info">Mendapatkan pesanan</div>';

                // Menggunakan backticks (`) untuk memungkinkan multiline strings dan interpolation
                const popupContent = `<div class="card_profile">
                                        <div class="profileImage">
                                        <svg viewBox="0 0 128 128"><circle r="60" fill="transparent" cy="64" cx="64"></circle><circle r="48" fill="transparent" cy="64" cx="64"></circle><path fill="#191919" d="m64 14a32 32 0 0 1 32 32v41a6 6 0 0 1 -6 6h-52a6 6 0 0 1 -6-6v-41a32 32 0 0 1 32-32z"></path><path opacity="1" fill="#191919" d="m62.73 22h2.54a23.73 23.73 0 0 1 23.73 23.73v42.82a4.45 4.45 0 0 1 -4.45 4.45h-41.1a4.45 4.45 0 0 1 -4.45-4.45v-42.82a23.73 23.73 0 0 1 23.73-23.73z"></path><circle r="7" fill="#fbc0aa" cy="65" cx="89"></circle><path fill="#4bc190" d="m64 124a59.67 59.67 0 0 0 34.69-11.06l-3.32-9.3a10 10 0 0 0 -9.37-6.64h-43.95a10 10 0 0 0 -9.42 6.64l-3.32 9.3a59.67 59.67 0 0 0 34.69 11.06z"></path><path opacity=".3" fill="#356cb6" d="m45 110 5.55 2.92-2.55 8.92a60.14 60.14 0 0 0 9 1.74v-27.08l-12.38 10.25a2 2 0 0 0 .38 3.25z"></path><path opacity=".3" fill="#356cb6" d="m71 96.5v27.09a60.14 60.14 0 0 0 9-1.74l-2.54-8.93 5.54-2.92a2 2 0 0 0 .41-3.25z"></path><path fill="#fff" d="m57 123.68a58.54 58.54 0 0 0 14 0v-25.68h-14z"></path><path stroke-width="14" stroke-linejoin="round" stroke-linecap="round" stroke="#fbc0aa" fill="none" d="m64 88.75v9.75"></path><circle r="7" fill="#fbc0aa" cy="65" cx="39"></circle><path fill="#ffd8ca" d="m64 91a25 25 0 0 1 -25-25v-16.48a25 25 0 1 1 50 0v16.48a25 25 0 0 1 -25 25z"></path><path fill="#191919" d="m91.49 51.12v-4.72c0-14.95-11.71-27.61-26.66-28a27.51 27.51 0 0 0 -28.32 27.42v5.33a2 2 0 0 0 2 2h6.81a8 8 0 0 0 6.5-3.33l4.94-6.88a18.45 18.45 0 0 1 1.37 1.63 22.84 22.84 0 0 0 17.87 8.58h13.45a2 2 0 0 0 2.04-2.03z"></path><path style="fill:none;stroke-linecap:round;stroke:#fff;stroke-miterlimit:10;stroke-width:2;opacity:.1" d="m62.76 36.94c4.24 8.74 10.71 10.21 16.09 10.21h5"></path><path style="fill:none;stroke-linecap:round;stroke:#fff;stroke-miterlimit:10;stroke-width:2;opacity:.1" d="m71 35c2.52 5.22 6.39 6.09 9.6 6.09h3"></path><circle r="3" fill="#515570" cy="62.28" cx="76"></circle><circle r="3" fill="#515570" cy="62.28" cx="52"></circle><ellipse ry="2.98" rx="4.58" opacity=".1" fill="#f85565" cy="69.67" cx="50.42"></ellipse><ellipse ry="2.98" rx="4.58" opacity=".1" fill="#f85565" cy="69.67" cx="77.58"></ellipse><g stroke-linejoin="round" stroke-linecap="round" fill="none"><path stroke-width="4" stroke="#fbc0aa" d="m64 67v4"></path><path stroke-width="2" stroke="#515570" opacity=".2" d="m55 56h-9.25"></path><path stroke-width="2" stroke="#515570" opacity=".2" d="m82 56h-9.25"></path></g><path opacity=".4" fill="#f85565" d="m64 84c5 0 7-3 7-3h-14s2 3 7 3z"></path><path fill="#f85565" d="m65.07 78.93-.55.55a.73.73 0 0 1 -1 0l-.55-.55c-1.14-1.14-2.93-.93-4.27.47l-1.7 1.6h14l-1.66-1.6c-1.34-1.4-3.13-1.61-4.27-.47z"></path></svg>
                                        </div>
                                        <div class="textContainer_profile">
                                            <p class="name_profile">${item.user.name}</p>
                                            ${statusBadge}
                                        </div>
                                        </div>`;
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
                setData();
                imgMenu();
                create();
                hapus();
                addRow();
                addData();
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
