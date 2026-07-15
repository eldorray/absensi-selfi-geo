{{-- Service worker registration only (no update banner).
     New worker installs and activates on next cold launch. --}}
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js').catch(function (e) {
                console.log('SW registration failed:', e);
            });
        });
    }
</script>
