<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    {{-- Base Meta Tags --}}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('img\cleora-small.png') }}" type="image/x-icon">

    {{-- Custom Meta Tags --}}
    @yield('meta_tags')

    {{-- Title --}}
    <title>
        @yield('title_prefix', config('adminlte.title_prefix', ''))
        @yield('title', config('adminlte.title', 'AdminLTE 3'))
        @yield('title_postfix', config('adminlte.title_postfix', ''))
    </title>

    {{-- Custom stylesheets (pre AdminLTE) --}}
    @yield('adminlte_css_pre')

    {{-- Base Stylesheets --}}
    @if(!config('adminlte.enabled_laravel_mix'))
        <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
        <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/core/main.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid/main.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid/main.css" rel="stylesheet" />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
        @if(config('adminlte.google_fonts.allowed', true))
            <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
        @endif
    @else
        <link rel="stylesheet" href="{{ mix(config('adminlte.laravel_mix_css_path', 'css/app.css')) }}">
    @endif

    {{-- Extra Configured Plugins Stylesheets --}}
    @include('adminlte::plugins', ['type' => 'css'])

    {{-- Livewire Styles --}}
    @if(config('adminlte.livewire'))
        @if(intval(app()->version()) >= 7)
            @livewireStyles
        @else
            <livewire:styles />
        @endif
    @endif

    {{-- Custom Stylesheets (post AdminLTE) --}}
    @yield('adminlte_css')

    {{-- Favicon --}}
    @if(config('adminlte.use_ico_only'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
    @elseif(config('adminlte.use_full_favicon'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
        <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('favicons/apple-icon-57x57.png') }}">
        <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('favicons/apple-icon-60x60.png') }}">
        <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('favicons/apple-icon-72x72.png') }}">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicons/apple-icon-76x76.png') }}">
        <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('favicons/apple-icon-114x114.png') }}">
        <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('favicons/apple-icon-120x120.png') }}">
        <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('favicons/apple-icon-144x144.png') }}">
        <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('favicons/apple-icon-152x152.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicons/apple-icon-180x180.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/favicon-16x16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicons/favicon-96x96.png') }}">
        <link rel="icon" type="image/png" sizes="192x192"  href="{{ asset('favicons/android-icon-192x192.png') }}">
        <link rel="manifest" crossorigin="use-credentials" href="{{ asset('favicons/manifest.json') }}">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="{{ asset('favicon/ms-icon-144x144.png') }}">
    @endif
</head>

<body class="@yield('classes_body')" @yield('body_data')>

    {{-- Body Content --}}
    @yield('body')
    <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
    {{-- Base Scripts --}}
    @if(!config('adminlte.enabled_laravel_mix'))
        <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
        <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
        <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
    @else
        <script src="{{ mix(config('adminlte.laravel_mix_js_path', 'js/app.js')) }}"></script>
    @endif

    {{-- Extra Configured Plugins Scripts --}}
    @include('adminlte::plugins', ['type' => 'js'])

    {{-- Livewire Script --}}
    @if(config('adminlte.livewire'))
        @if(intval(app()->version()) >= 7)
            @livewireScripts
        @else
            <livewire:scripts />
        @endif
    @endif

    {{-- Custom Scripts --}}
    @yield('adminlte_js')
    <script>
        @if (Session::has('message'))
            toastr.{{ Session::get('alert') }}("{{ Session::get('message') }}")
        @endif

        Inputmask.extendAliases({
            "myNumeric": {
                alias: 'numeric',
                groupSeparator: '.',
                radixPoint: ',',
                autoGroup: true,
                digits: 2,
                digitsOptional: true,
                placeholder: '0',
                autoUnmask: true,
                removeMaskOnSubmit: true,
                onUnMask: function (maskedValue, unmaskedValue, opts) {
                    if (unmaskedValue === "" && opts.nullable === true) {
                        return unmaskedValue;
                    }
                    let processValue = maskedValue.replace(opts.prefix, "");
                    processValue = processValue.replace(opts.suffix, "");
                    processValue = processValue.replace(new RegExp(escapeRegExp(opts.groupSeparator), "g"), "");
                    if (opts.radixPoint !== "" && processValue.indexOf(opts.radixPoint) !== -1)
                        processValue = processValue.replace(new RegExp(escapeRegExp(opts.radixPoint), "g"), ".");
                    return processValue;
                },
            },
            "myNumericNoDecimals": {
                alias: 'numeric',
                groupSeparator: '.',
                autoGroup: true,
                placeholder: '0',
                autoUnmask: true,
                removeMaskOnSubmit: true,
                digits: 0, // Ensure no decimals are allowed
            }
        });

        $('.money').inputmask("myNumericNoDecimals");
        $('.moneyDecimal').inputmask("myNumeric");

        // Function to escape special characters in a regular expression pattern
        function escapeRegExp(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); // $& means the whole matched string
        }

        $('.singleDate').daterangepicker({
            autoApply: true,
            singleDatePicker: true,
            showDropdowns: true,
            locale: {
                format: 'DD/MM/YYYY'
            }
        });

        // Get the start of the current month
        const startDate = moment().startOf('month');
        // Get the end of the current month
        const endDate = moment().endOf('month');

        $('.rangeDate').daterangepicker({
            startDate: startDate,
            endDate: endDate,
            autoApply: true,
            showDropdowns: true,
            locale: {
                format: 'DD/MM/YYYY'
            },
            maxSpan: {
                days: 31 // Maximum span of 31 days, equivalent to one month
            }
        });

        $('.rangeDateNoLimit').daterangepicker({
            startDate: startDate,
            endDate: endDate,
            autoApply: true,
            showDropdowns: true,
            locale: {
                format: 'DD/MM/YYYY'
            }
        });

        $('.monthYear').datepicker({
            format: "mm/yyyy",
            viewMode: "months",
            minViewMode: "months",
        }).on('changeDate', function (e) {
            let $this = $(this);
            setTimeout(function() {
                $this.change();
            }, 0);
            $(this).datepicker('hide'); // Hide the datepicker after selection
        })

        function errorAjaxValidation(xhr, status, error, selector)
        {
            // Check if the response status is Unprocessable Entity (422)
            if (xhr.status === 422) {
                // Parse the response JSON to get validation errors
                let errors = xhr.responseJSON.errors;

                // Loop through errors and display them next to the corresponding fields
                $.each(errors, function(field, message) {
                    selector.removeClass('d-none');
                    selector.html('<span class="text-danger">' + message[0] + '</span>'); // Update error message for the field
                });
            } else {
                // Handle other types of errors
                console.log('Error:', error);
            }
        }

        function errorImportAjaxValidation(xhr, status, error, selector)
        {
            // Check if the response status is Unprocessable Entity (422)
            if (xhr.status === 422) {
                // Parse the response JSON to get validation errors
                let errors = xhr.responseJSON.errors;

                let output = '';

                // Iterate over the outer array using $.each()
                $.each(errors, function(index, errorArray) {
                        selector.removeClass('d-none');

                    // Iterate over the inner array using $.each()
                    $.each(errorArray, function(innerIndex, errorMessage) {
                        output += '<span class="text-danger">' + errorMessage + '</span><br>';
                    });
                });

                // Append the output to the div with id "output"
                selector.html(output);
            } else {
                // Handle other types of errors
                console.log('Error:', error);
            }
        }

        function deleteAjax(route, id, table)
        {
            Swal.fire({
                title: '{{ trans('labels.are_you_sure') }}',
                text: '{{ trans('labels.not_be_able_to_recover') }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '{{ trans('buttons.confirm_swal') }}',
                cancelButtonText: '{{ trans('buttons.cancel_swal') }}',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // User confirmed, send delete request
                    $.ajax({
                        type: 'DELETE',
                        url: route.replace(':id', id),
                        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                        success: function (response, textStatus, jqXHR) {
                            // Item deleted successfully, display success message
                            Swal.fire(
                                '{{ trans('labels.success') }}',
                                '{{ trans('messages.success_delete') }}',
                                'success'
                            ).then((result) => {
                                // Reload page after deletion
                                table.ajax.reload();
                            });
                        },
                        error: function (xhr, status, error) {
                            if (xhr.status === 422) {
                                Swal.fire(
                                    '{{ trans('labels.failed') }}',
                                    xhr.responseJSON.message,
                                    'error'
                                );
                            } else {
                                Swal.fire(
                                    '{{ trans('labels.failed') }}',
                                    '{{ trans('messages.error_delete') }}',
                                    'error'
                                );
                            }
                        }
                    })
                }
            })
        }

        // Predefined colors
        const predefinedColors = [
            'rgba(255, 99, 132, 0.5)',
            'rgba(54, 162, 235, 0.5)',
            'rgba(255, 206, 86, 0.5)',
            'rgba(75, 192, 192, 0.5)',
            'rgba(153, 102, 255, 0.5)',
            'rgba(255, 159, 64, 0.5)',
            'rgba(255, 0, 0, 0.5)',
            'rgba(0, 255, 0, 0.5)',
            'rgba(0, 0, 255, 0.5)',
            'rgba(255, 255, 0, 0.5)',
            'rgba(255, 0, 255, 0.5)',
            'rgba(0, 255, 255, 0.5)',
            'rgba(128, 0, 0, 0.5)',
            'rgba(0, 128, 0, 0.5)',
            'rgba(0, 0, 128, 0.5)',
            'rgba(128, 128, 0, 0.5)',
            'rgba(128, 0, 128, 0.5)',
            'rgba(0, 128, 128, 0.5)',
            'rgba(192, 192, 192, 0.5)',
            'rgba(128, 128, 128, 0.5)'
            // Add more colors as needed
        ];

        // Function to generate colors from the predefined array
        function generatePredefinedColors(numColors) {
            const colors = [];
            for (let i = 0; i < numColors; i++) {
                colors.push(predefinedColors[i % predefinedColors.length]); // Cycle through predefined colors
            }
            return colors;
        }
    </script>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>

</html>
