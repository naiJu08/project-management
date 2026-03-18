<!DOCTYPE html>
<html>
<head>
    <title>User Chat</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @livewireStyles
</head>

<body>

@yield('content')

@livewireScripts

</body>
</html>