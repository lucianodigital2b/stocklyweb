@php
$config = [
    'githubAuth' => config('services.github.client_id'),
];

@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{config('app.name')}}</title>
    <!--begin::Fonts(mandatory for all pages)-->
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    
    @vite(['resources/js/main.js', 'resources/assets/styles.scss'])

    <script>
        window.config = @json($config);
    </script>
</head>
<body>
    <div id="app"></div>
</body>
</html>