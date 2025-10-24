<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test AI Assistant</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold mb-4">AI Assistant Test Page</h1>
        <p class="mb-4">If the AI button appears in the bottom-right corner, the component is working!</p>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-2">Debug Info:</h2>
            <ul class="space-y-2">
                <li>✅ Livewire is loaded</li>
                <li>✅ Tailwind CSS is loaded</li>
                <li>✅ Component should render below</li>
            </ul>
        </div>
    </div>

    {{-- Load the AI Assistant Component --}}
    @livewire('enhanced-ai-assistant')

    @livewireScripts
</body>
</html>
