<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Frame</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
{{--    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />--}}
    @include('includes.css')
{{--    @toastr_css--}}
    @stack('css')
{{--    @livewireStyles--}}
{{--    @livewireScripts--}}
</head>

<body >
<div class="wrapper">
    @include('includes.sidemenu')
    <div class="main">
        @include('includes.header')
        @yield('content')
        @include('includes.footer')
    </div>
</div>

@include('includes.js')
<script type="text/javascript">
    $(document).ready(function (){
        $(document).on("click", ".deleteDialog ", function () {
            //$.noConflict();
            $('.modal-dialog #action').attr('action', $(this).data('url'));
            $(".modal-body #target").val($(this).data('target'));
            $(".modal").modal('show')
        });
    });

    // console.log(Livewire)
</script>
@yield('js')

</body>
</html>

