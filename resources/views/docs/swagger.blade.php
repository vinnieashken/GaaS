<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swagger UI</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/swagger/swagger-ui.css') }}" />
    <script src="{{ asset('assets/swagger/swagger-ui-bundle.js') }}"></script>
    <script src="{{ asset('assets/swagger/swagger-ui-standalone-preset.js') }}"></script>
    <style>
        /* Hide the topbar input and explore button */
        .topbar-wrapper .download-url-wrapper {
            display: none !important;
        }
        .url{
            display: none !important;
        }

        .version-links {
            padding: 10px;
            background: #f7f7f7;
            border-bottom: 1px solid #ddd;
        }

        .version-links a {
            margin-right: 15px;
            font-weight: bold;
            color: #0366d6;
            text-decoration: none;
        }

        .version-links a:hover {
            text-decoration: underline;
        }

        .topbar-wrapper .download-url-wrapper {
            display: none !important; /* Hide the Explore bar */
        }
    </style>
</head>
<body>

@if(count($versions))
<div style="padding: 10px; background: #f7f7f7; border-bottom: 1px solid #ddd;">
    <label for="version-select" style="margin-right: 10px; font-weight: bold;">API Version:</label>
    <select id="version-select" onchange="onVersionChange(this)">
        @foreach($versions as $ver)
            <option value="{{ url('/docs/' . $ver) }}" {{ $ver === $version ? 'selected' : '' }}>
                {{ strtoupper($ver) }}
            </option>
        @endforeach
    </select>
</div>
@endif
<div id="swagger-ui"></div>

<script>
    window.onload = function() {
        const ui = SwaggerUIBundle({
            url: "{{ route('docs.swagger',$version) }}",  // The route that returns your OpenAPI JSON
            dom_id: '#swagger-ui',
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],
            //layout: "StandaloneLayout",
            layout: "BaseLayout"
        });
    }

    function onVersionChange(select) {
        const url = select.value;
        window.location.href = url;
    }
</script>
</body>
</html>

