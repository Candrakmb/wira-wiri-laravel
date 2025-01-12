<script>
    const chartDataDone = @json($chartDataDone);
    const chartDataDeny = @json($chartDataDeny);
    const chartDataPendapatan = @json($chartDataPendapatan);
    var ctx1 = document.getElementById("chart-order-selesai").getContext("2d");
    var ctx2 = document.getElementById("chart-order-batal").getContext("2d");
    var ctx3 = document.getElementById("chart-pendapatan").getContext("2d");


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
                    "url": "/dashboard/table",
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
                        data: 'invoice_number',
                        name: 'invoice_number',
                        class: 'text-left'
                    },
                    {
                        data: 'pelanggan',
                        name: 'pelanggan',
                        class: 'text-left'
                    },
                    {
                        data: 'driver',
                        name: 'driver',
                        class: 'text-left'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        class: 'text-left'
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


        var muatUlang = function() {
            $('#btn-muat-ulang').on('click', function() {
                $('#table').DataTable().ajax.reload();
            });
        }

    new Chart(ctx1, {
      type: "line",
      data:chartDataDone,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5],
              color: 'rgba(255, 255, 255, .2)'
            },
            ticks: {
              display: true,
              color: '#f8f9fa',
              padding: 10,
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: 'normal',
                lineHeight: 2
              },
              stepSize: 1, // Tampilkan angka sebagai integer
              callback: function (value) {
                    // Hanya tampilkan angka positif, tanpa koma
                    return Number.isInteger(value) && value >= 0 ? value : '';
              }
            }
          },
          x: {
            grid: {
              drawBorder: false,
              display: false,
              drawOnChartArea: false,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              color: '#f8f9fa',
              padding: 10,
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
        },
      },
    });



    new Chart(ctx2, {
      type: "line",
      data:chartDataDeny,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5],
              color: 'rgba(255, 255, 255, .2)'
            },
            ticks: {
              display: true,
              color: '#f8f9fa',
              padding: 10,
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: 'normal',
                lineHeight: 2
              },
              stepSize: 1, // Tampilkan angka sebagai integer
              callback: function (value) {
                    // Hanya tampilkan angka positif, tanpa koma
                    return Number.isInteger(value) && value >= 0 ? value : '';
              }
            }
          },
          x: {
            grid: {
              drawBorder: false,
              display: false,
              drawOnChartArea: false,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              color: '#f8f9fa',
              padding: 10,
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
        },
      },
    });



    new Chart(ctx3, {
      type: "line",
      data: chartDataPendapatan,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5],
              color: 'rgba(255, 255, 255, .2)'
            },
            ticks: {
              display: true,
              padding: 10,
              color: '#f8f9fa',
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
          x: {
            grid: {
              drawBorder: false,
              display: false,
              drawOnChartArea: false,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              color: '#f8f9fa',
              padding: 10,
              font: {
                size: 14,
                weight: 300,
                family: "Roboto",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
        },
      },
    });
        return {
            init: function() {
                table();
                muatUlang();
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
