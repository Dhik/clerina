@extends('adminlte::page')

@section('title', 'Demography')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class='tableauPlaceholder' id='viz1734936908846' style='position: relative'>
                        <noscript>
                            <a href='#'>
                                <img alt='Customers Analysis' src='https://public.tableau.com/static/images/4Y/4YJX25T8D/1_rss.png' style='border: none' />
                            </a>
                        </noscript>
                        <object class='tableauViz' style='display:none;'>
                            <param name='host_url' value='https%3A%2F%2Fpublic.tableau.com%2F' />
                            <param name='embed_code_version' value='3' />
                            <param name='path' value='shared/4YJX25T8D' />
                            <param name='toolbar' value='yes' />
                            <param name='static_image' value='https://public.tableau.com/static/images/4Y/4YJX25T8D/1.png' />
                            <param name='animate_transition' value='yes' />
                            <param name='display_static_image' value='yes' />
                            <param name='display_spinner' value='yes' />
                            <param name='display_overlay' value='yes' />
                            <param name='display_count' value='yes' />
                            <param name='language' value='en-US' />
                            <param name='filter' value='publish=yes' />
                        </object>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script type='text/javascript'>
        var divElement = document.getElementById('viz1734936908846');
        var vizElement = divElement.getElementsByTagName('object')[0];
        
        if (divElement.offsetWidth > 800) {
            vizElement.style.width = '100%';
            vizElement.style.height = (divElement.offsetWidth * 0.75) + 'px';
        } else if (divElement.offsetWidth > 500) {
            vizElement.style.width = '100%';
            vizElement.style.height = (divElement.offsetWidth * 0.75) + 'px';
        } else {
            vizElement.style.width = '100%';
            vizElement.style.height = '1127px';
        }
        
        var scriptElement = document.createElement('script');
        scriptElement.src = 'https://public.tableau.com/javascripts/api/viz_v1.js';
        vizElement.parentNode.insertBefore(scriptElement, vizElement);
    </script>
@stop