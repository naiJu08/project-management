<div>
    <button
        type="button"
        aria-label="Toggle dark mode"
        class="fixed z-10 bottom-1 right-16 w-12 h-12 flex items-center justify-center rounded-full ai-btn-primary focus:outline-none focus:ring-2 focus:ring-accent-400"
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
        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364-6.364l-1.414 1.414M7.05 16.95l-1.414 1.414M18.364 18.364l-1.414-1.414M7.05 7.05L5.636 5.636"/>
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
</div>
