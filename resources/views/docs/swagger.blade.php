<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swagger UI</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/swagger/swagger-ui.css') }}" />
    <script src="{{ asset('assets/swagger/swagger-ui-bundle.js') }}"></script>
    <script src="{{ asset('assets/swagger/swagger-ui-standalone-preset.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>
<body>
<div id="swagger-ui"></div>

<script>
    window.onload = function() {
        const ui = SwaggerUIBundle({
            url: "{{ route('docs.swagger') }}",  // The route that returns your OpenAPI JSON
            dom_id: '#swagger-ui',
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],
            layout: "StandaloneLayout"
        });
    }

    $(document).ready(function (){
        $("#download-url-input").attr('readonly','readonly');
    });
</script>
</body>
</html>

