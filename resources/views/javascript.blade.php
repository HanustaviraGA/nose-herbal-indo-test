<script>
    $(document).ready(function() {
        let searchDelay;
        let $searchInput = $('#customSearchInput');
        // Bind keyup event to custom search input with a delay
        $searchInput.keyup(function() {
            clearTimeout(searchDelay);
            let searchText = $searchInput.val();
            searchDelay = setTimeout(function() {
                table.search(searchText).draw();
            }, 300); // Adjust the delay time (in milliseconds) as needed
        });
        init_table();
    });

    function init_table() {
        if ($.fn.DataTable.isDataTable('#tableUser')) {
			$('#tableUser').DataTable().destroy();
		}
        table = $('#tableUser').DataTable({
            responsive: true,
            serverSide: true,
            processing: true,
            pageLength: 10,
            // select: 'single',
            ajax: {
                url: '{{ route('init_table') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: function (d) {
                    d.search.value = $('#customSearchInput').val();
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 419 || xhr.status === 401) {
                        SUPER.showMessage({
                            success: false,
                            message: 'Sesi telah berakhir',
                            title: 'Gagal'
                        });
                        setLogout();
                    } else {
                        // Handle other error cases
                        // For example, you can display an error message to the user
                        console.error(error);
                    }
                }
            },
            columns: [
                {
                    data: 'no',
                    name: 'no',
                    orderable: true,
                },
                {
                    data: 'name',
                    name: 'name',
                    orderable: true,
                },
                {
                    data: 'email',
                    name: 'email',
                    orderable: true,
                },
                {
                    data: null,
                    orderable: false,
                    visible: true,
                    render: function (data, type, full, meta) {
                        var btn_aksi = '';

                        btn_aksi += `<a href="javascript:;" class="btn btn-sm btn-clean btn-icon btn-icon-md mr-2" title="Edit" onclick="onEdit(this)" data-id="` + data.id + `">
                            <i class="la la-edit"></i> Edit
                        </a>`;

                        btn_aksi += `<a href="javascript:;" class="btn ml-5 btn-sm btn-clean btn-icon btn-icon-md kt-font-bold kt-font-danger" style="margin-left: 15px" onclick="onDestroy(this)" data-id="` + data.id + `" title="Hapus" >
                            <span class="la la-trash"></span> Hapus
                        </a>`;

                        return btn_aksi;
                    }
                }
            ],
            fnDrawCallback: function(oSettings) {
                var cnt = 0;
                $("tr", this).css('cursor', 'pointer');
                $("tbody tr", this).each(function(i, v) {
                    $(v).on('click', function() {
                        if ($(this).hasClass('selected')) {
                            --cnt;
                            $(v).removeClass('selected');
                            $(v).removeAttr('checked');
                            $('input[name=checkbox]', v).prop('checked', false);
                            $(v).removeClass('row_selected');
                        } else {
                            ++cnt;
                            $('input[name=checkbox]', v).prop('checked', true);
                            $('input[name=checkbox]', v).data('checked', 'aja');
                            $(v).addClass('selected');
                            $(v).addClass('row_selected asli');
                        }

                        if (cnt > 0) {
                            $('.disable').attr('disabled', false);
                        } else {
                            $('.disable').attr('disabled', true);
                        }
                    });
                });
            },
        });
    }

    function onAdd(){
        blockPage();
        $('#formUser').trigger('reset');
        $('#button_reset').show();
        $('#jdl_form_user').text('Form Tambah User').trigger('change');
        $('#password').attr('required', true).trigger('change');
        $('#lbl-pw').addClass('required').trigger('change');
        SUPER.switchForm({
            tohide: 'table_data',
            toshow: 'table_data_detail'
        });
        unblockPage();
    }

    function onBack(){
        blockPage();
        $('#formUser').trigger('reset');
        $('#button_reset').show();
        $('#jdl_form_user').text('Form Tambah User').trigger('change');
        $('#password').attr('required', true).trigger('change');
        $('#lbl-pw').addClass('required').trigger('change');
        SUPER.switchForm({
            tohide: 'table_data_detail',
            toshow: 'table_data'
        });
        unblockPage();
    }

    function onDestroy(element){
        var id = $(element).data('id');
        SUPER.confirm({
			message: "Apa Anda yakin ingin menghapus data ini?",
			callback: (result) => {
				if (result) {
                    $.ajax({
                        url: '{{ route('delete') }}',
                        type: 'DELETE',
                        headers:{
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: {
                            'id': id
                        },
                        success: function(response) {
                            if(response.success) {
                                SUPER.showMessage({
                                    success: true,
                                    message: 'Berhasil melakukan penghapusan data',
                                    title: 'Berhasil'
                                });
                            }else{
                                SUPER.showMessage();
                            }
                            init_table();
                        }
                    });
                }
			}
		});
    }

    function onSave(form){
        SUPER.saveForm({
            element: form,
            checker: 'id',
            add_route: '{{ route('create') }}',
            update_route: '{{ route('update') }}',
            onBack: false,
            reInitTable: true,
        });
    }

    function onEdit(element){
        blockPage();
        var id_usr = element.getAttribute('data-id');
        $.ajax({
            url: "{{ route('read') }}",
            type: 'POST',
            data:{
                id: id_usr,
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            dataType: 'json',
            success: function(res){
                $.each(res.data, function(key, value) {
                    $('#'+key).val(value).trigger('change');
                });
                $('#password').val('').trigger('change');
                $('#id').val(res.data.id).trigger('change');
                $('#jdl_form_user').text('Form Edit User').trigger('change');
                $('#password').attr('required', false).trigger('change');
                $('#lbl-pw').removeClass('required').trigger('change');
                $('#button_reset').hide();
                SUPER.switchForm({
                    tohide: 'table_data',
                    toshow: 'table_data_detail'
                });
                unblockPage();
            },
            error: function(xhr, status, error) {
                unblockPage();
                SUPER.showMessage();
                console.log(error);
            }
        });
    }

    function onReset(){
        $('#formUser').trigger('reset');
    }

    // Utility
    function blockPage(message = 'Memuat...'){
        $.blockUI({
            message: `<div class="blockui-message" style="z-index: 9999"><span class="spinner-border text-primary"></span> ${message} </div>`,
            css: {
                border: 'none',
                backgroundColor: 'rgba(47, 53, 59, 0)',
                'z-index': 9999
            }
        });
    }

    function unblockPage(delay = 500){
        window.setTimeout(function () {
            $.unblockUI();
        }, delay);
    }

    $.fn.serializeObject = function(){
		var o = {};
		var a = this.serializeArray();
		$.each(a, function() {
			if (o[this.name]) {
				if (!o[this.name].push) {
					o[this.name] = [o[this.name]];
				}
				o[this.name].push(this.value || '');
			} else {
				o[this.name] = this.value || '';
			}
		});
		return o;
	};

    var SUPER = function(){
        return {
            confirm: function (config) {

                config = $.extend(true, {
                    title: 'Information',
                    message: null,
                    size: 'small',
                    type: 'blue',
                    confirmLabel: '<i class="fa fa-check"></i> Yes',
                    confirmClassName: 'btn btn-focus btn-success m-btn m-btn--pill m-btn--air',
                    cancelLabel: '<i class="fa fa-times"></i> No',
                    cancelClassName: 'btn btn-focus btn-danger m-btn m-btn--pill m-btn--air',
                    showLoaderOnConfirm: false,
                    allowOutsideClick: true,
                    callback: function () { }
                }, config);

                $.confirm({
                    title: config.title,
                    content: config.message,
                    theme: 'material',
                    type: config.type,
                    buttons: {
                        ok: {
                            text: "ok!",
                            btnClass: 'btn-primary',
                            keys: ['enter'],
                            action: function () {
                                config.callback(true);
                            }
                        }, cancel: function () {
                            config.callback(false);
                        }
                    }
                });
            },

            showMessage: function (config) {
                config = $.extend(true, {
                    success: false,
                    message: 'System error, please contact the Administrator',
                    title: 'Failed',
                    time: 5000,
                    sticky: false,
                    allowOutsideClick: true,
                    toast: false,
                    type: 'blue',
                    btnClass: 'btn-primary',
                    callback: function () { },
                }, config);
                if (config.success == true) {
                    $.confirm({
                        title: (config.title == "Failed") ? "Success" : config.title,
                        content: config.message,
                        theme: 'material',
                        type: config.type,
                        buttons: {
                            ok: {
                                text: "ok!",
                                btnClass: config.btnClass,
                                keys: ['enter'],
                                action: function () {
                                    config.callback(true);
                                }
                            }
                        }
                    });
                } else {
                    $.confirm({
                        title: config.title,
                        content: config.message,
                        theme: 'material',
                        type: 'red',
                        buttons: {
                            ok: {
                                text: "ok!",
                                btnClass: config.btnClass,
                                keys: ['enter'],
                                action: function () {
                                    config.callback(true);
                                }
                            }
                        }
                    });
                }
            },

            trim_string: function(originalString, maxlength = 30){
                var truncatedString = originalString.length > maxlength
                ? originalString.substring(0, maxlength) + '...'
                : originalString;
                return truncatedString;
            },

            saveForm: function(config){
                config = $.extend(true, {
                    element  : null,
                    checker : null,
                    add_route: null,
                    update_route : null,
                    onBack: null,
                    reInitTable: null,
                    callback: function(args){}
                }, config);
                var id = $('#'+config.checker).val();
                // Penentuan URL dan Tipe Protokol
                var alamat = '';
                var protocol = '';
                if(jQuery.isEmptyObject(id)){
                    alamat = config.add_route;
                    protocol = 'POST';
                }else{
                    alamat = config.update_route;
                    protocol = 'PUT';
                }
                // Konfirmasi
                SUPER.confirm({
                    message: 'Anda akan melakukan penyimpanan/pengubahan data. Anda yakin untuk menyimpan ?',
                    callback: (result) => {
                        if(result){
                            blockPage();
                            $.ajax({
                                url: alamat,
                                type: protocol,
                                data: $('[name=' + config.element + ']').serializeObject(),
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                success: function(lar){
                                    if(lar.success == true){
                                        SUPER.showMessage({
                                            success: true,
                                            message: 'Penyimpanan sukses !',
                                            title: 'Sukses'
                                        });
                                        $('#'+config.element)[0].reset();
                                        if(config.onBack != null){
                                            onBack();
                                        }
                                    }else{
                                        SUPER.showMessage({
                                            success: false,
                                            message: lar.message,
                                            title: 'Gagal'
                                        });
                                        onRefresh();
                                    }
                                },error: function(res){
                                    SUPER.showMessage({
                                        success: false,
                                        message: res.responseJSON.message,
                                        title: 'Gagal'
                                    });
                                }
                            });
                            if(config.reInitTable != null){
                                init_table();
                            }
                            unblockPage();
                        }else{
                            SUPER.showMessage({
                                success: false,
                                message: 'Penyimpanan dibatalkan',
                                title: 'Batal'
                            });
                        }
                    }
                });
            },

            switchForm: function(config){
                config = $.extend(true, {
                    speed: 'fast',
                    easing: 'swing',
                    callback: function() {},
                    tohide: 'table_data',
                    toshow: 'form_data',
                    animate: null,
                }, config);

                if (config.animate!==null)
                {
                    if (config.animate==='fade')
                    {
                        $("." + config.tohide).fadeOut(config.speed, function() {
                            $("." + config.toshow).fadeIn(config.speed, config.callback)
                        });
                    }
                    else if (config.animate==='toogle')
                    {
                        $("." + config.tohide).fadeToggle(config.speed, function() {
                            $("." + config.toshow).fadeToggle(config.speed, config.callback)
                        });
                    }
                    else if (config.animate==='slide')
                    {
                        $("." + config.tohide).slideUp(config.speed, function(){
                            $("." + config.toshow).slideDown(config.speed,config.callback);
                        });
                    }
                    else{
                        $("." + config.tohide).fadeOut(config.speed, function() {
                            $("." + config.toshow).fadeIn(config.speed, config.callback)
                        });
                    }
                }
                else
                {
                    $("." + config.tohide).fadeOut(config.speed, function() {
                        $("." + config.toshow).fadeIn(config.speed, config.callback)
                    });
                }

                $('html,body').animate({
                    scrollTop: 0 /*pos + (offeset ? offeset : 0)*/
                }, 'slow');
            },

            formatDate: function (dateString) {
                var date = new Date(dateString);
                var months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                var day = date.getDate();
                var month = months[date.getMonth()];
                var year = date.getFullYear();
                var hours = date.getHours();
                var minutes = date.getMinutes();
                var seconds = date.getSeconds();
                // Adjusting to local timezone
                var timezoneOffset = date.getTimezoneOffset() / 60;
                hours += timezoneOffset;
                // Correcting hours if negative or exceeding 24
                if (hours < 0) {
                    hours += 24;
                    day -= 1; // Subtract one day
                }
                else if (hours >= 24) {
                    hours -= 24;
                    day += 1; // Add one day
                }
                // Adding leading zeros if needed
                if (day < 10) day = '0' + day;
                if (hours < 10) hours = '0' + hours;
                if (minutes < 10) minutes = '0' + minutes;
                if (seconds < 10) seconds = '0' + seconds;
                var formattedDate = day + ' ' + month + ' ' + year + ' ' + hours + ':' + minutes + ':' + seconds;
                return formattedDate;
            },
        }
    }();
</script>
