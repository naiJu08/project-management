<button
    type="button"
    aria-label="Toggle dark mode"
    class="inline-flex items-center justify-center h-9 w-9 rounded-md border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
    onclick="(function(){
        const root = document.documentElement;
        const current = root.classList.contains('dark');
        if (current) {
            root.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        } else {
            root.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        }
    })()"
>
    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364-6.364l-1.414 1.414M7.05 16.95l-1.414 1.414M18.364 18.364l-1.414-1.414M7.05 7.05L5.636 5.636" />
    </svg>
</button>
<script>
    (function() {
        try {
            const persisted = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const shouldDark = persisted ? persisted === 'dark' : prefersDark;
            const root = document.documentElement;
            if (shouldDark) root.classList.add('dark'); else root.classList.remove('dark');
        } catch (e) {}
    })();
</script>
