{{-- Applies the stored/system theme before first paint to avoid a flash. --}}
<script>
    (function () {
        try {
            var mode = localStorage.getItem('theme') || 'system';
            var dark = mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
        } catch (e) {}
    })();
</script>
