@extends('adminlte::page')

@section('title', trans('labels.key_opinion_leader'))

@section('content_header')
    <h1>{{ trans('labels.add') }} {{ trans('labels.key_opinion_leader') }}</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <input type="button" class="btn btn-outline-primary" value="Add new row" onclick="$('#spreadsheet').jspreadsheet('insertRow')" />
                        <button class="btn btn-primary" id="btnSave">{{trans('buttons.save')}}</button>
                    </div>
                    <div class="card-body">
                        <div class="form-group d-none" id="errorSubmit"></div>
                        <div class="table-responsive">
                            <div id="spreadsheet"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        const errorSubmit = $('#errorSubmit');
        let channels = {!! json_encode($channels) !!};
        let niches = {!! json_encode($niches) !!};
        let skinTypes = {!! json_encode($skinTypes) !!};
        let skinConcerns = {!! json_encode($skinConcerns) !!};
        let contentTypes = {!! json_encode($contentTypes) !!};
        let marketingUsers = {!! json_encode($marketingUsers) !!};

        $('#btnSave').click(function () {
           let data =  $('#spreadsheet').jspreadsheet('getData')

            $.ajax({
                url: '{{ route('kol.store-excel') }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    data: data
                },
                success: function (response) {
                    errorSubmit.empty();

                    Swal.fire(
                        '{{ trans('labels.success') }}',
                        '{{ trans('messages.success_save', ['model' => trans('labels.key_opinion_leader')]) }}',
                        'success'
                    ).then(() => {
                        toastr.success('{{ trans('messages.success_save', ['model' => trans('labels.key_opinion_leader')]) }}');
                        window.location.href = "{{ route('kol.index') }}";
                    });
                },
                error: function(xhr, status, error) {

                    // Check if the response status is Unprocessable Entity (422)
                    if (xhr.status === 422) {
                        // Parse the response JSON to get validation errors
                        let errors = xhr.responseJSON.errors;

                        errorSubmit.removeClass('d-none');
                        errorSubmit.empty();

                        if (errorSubmit.parent().is('ul')) {
                            errorSubmit.unwrap();
                        }

                        // Loop through errors and display them next to the corresponding fields
                        $.each(errors, function(field, message) {
                            errorSubmit.append('<li class="text-danger">' + message[0] + '</li>'); // Update error message for the field
                        });

                        errorSubmit.wrap('<ul></ul>');
                    } else {
                        // Handle other types of errors
                        console.log('Error:', error);
                    }
                }
            });
        });

        $('#spreadsheet').jspreadsheet({
            tableOverflow:true,
            tableWidth: '100%',
            minDimensions:[21, 1],
            allowInsertColumn: false, // Disable adding columns
            allowDeleteColumn: false, // Disable deleting columns
            allowRenameColumn: false, // Disable renaming columns
            data: [],
            columns: [
                {type: 'dropdown', title: 'Channel*', width: 120, source: channels},
                {type: 'text', title: 'Username*', width: 120},
                {type: 'dropdown', title: 'Niche*', width: 120, source: niches},
                {type: 'text', title: 'Average view*', width: 200, mask:'#.##0'},
                {type: 'dropdown', title: 'Skin type*', width: 120, source: skinTypes},
                {type: 'dropdown', title: 'Skin concern*', width: 120, source: skinConcerns},
                {type: 'dropdown', title: 'Jenis Konten*', width: 120, source: contentTypes},
                {type: 'text', title: 'Rate harga per slot*', width: 200, mask:'#.##0'},
                {type: 'dropdown', title: 'PIC Contact*', width: 120, source: marketingUsers},
                {type: 'text', title: 'Nama', width: 120},
                {type: 'text', title: 'Alamat', width: 120},
                {type: 'text', title: 'Nomor Telepon', width: 120},
                {type: 'text', title: 'Nama bank', width: 120},
                {type: 'text', title: 'No rekening', width: 120},
                {type: 'text', title: 'Rekening atas nama', width: 120},
                {type: 'checkbox', title: 'NPWP', width: 120},
                {type: 'text', title: 'No NPWP', width: 120},
                {type: 'text', title: 'NIK', width: 120},
                {type: 'text', title: 'Notes', width: 120},
                {type: 'checkbox', title: 'Pengiriman produk', width: 150},
                {type: 'text', title: 'Nama produk', width: 120},
            ],
        });
    </script>
@endsection
