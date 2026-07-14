{{-- Service worker registration + "update available" prompt.
     Shared by mobile & app layouts so installed PWA users get notified. --}}
<div id="pwa-update-banner"
    style="display:none;position:fixed;left:50%;bottom:20px;transform:translateX(-50%);z-index:9999;
           max-width:calc(100vw - 32px);width:360px;padding:14px 16px;border-radius:16px;
           background:rgba(24,24,37,0.92);color:#f1f5f9;backdrop-filter:blur(12px);
           box-shadow:0 12px 40px -8px rgba(0,0,0,0.6);
           font-family:'Plus Jakarta Sans',system-ui,sans-serif;
           display:flex;align-items:center;gap:12px;">
    <img src="/images/icons/icon-192.png" alt="" width="36" height="36" style="border-radius:10px;flex-shrink:0;">
    <div style="flex:1;min-width:0;">
        <div style="font-weight:700;font-size:14px;line-height:1.2;">Pembaruan tersedia</div>
        <div style="font-size:12px;color:#94a3b8;">Muat ulang untuk versi & ikon terbaru.</div>
    </div>
    <button type="button" onclick="window.__pwaApplyUpdate()"
        style="flex-shrink:0;border:0;cursor:pointer;padding:8px 14px;border-radius:10px;
               background:#4f46e5;color:#fff;font-weight:600;font-size:13px;">Perbarui</button>
</div>

<script>
    (function () {
        if (!('serviceWorker' in navigator)) return;

        var registration = null;
        var refreshing = false;

        function reloadOnce() {
            if (refreshing) return;
            refreshing = true;
            window.location.reload();
        }

        // Fires when the new worker takes control -> reload to pick up fresh assets.
        navigator.serviceWorker.addEventListener('controllerchange', reloadOnce);

        function showBanner(worker) {
            var banner = document.getElementById('pwa-update-banner');
            if (banner) banner.style.display = 'flex';
            window.__pwaApplyUpdate = function () {
                if (banner) banner.style.display = 'none';
                var w = (registration && registration.waiting) || worker;
                if (w) w.postMessage({ type: 'SKIP_WAITING' });
                // Fallback: controllerchange is unreliable on some platforms
                // (iOS standalone). Give the new worker time to activate, then
                // reload anyway so the fresh cache is served.
                setTimeout(reloadOnce, 1500);
            };
        }

        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js').then(function (reg) {
                registration = reg;
                // Already a new SW waiting from a previous visit.
                if (reg.waiting && navigator.serviceWorker.controller) {
                    showBanner(reg.waiting);
                }
                reg.addEventListener('updatefound', function () {
                    var nw = reg.installing;
                    if (!nw) return;
                    nw.addEventListener('statechange', function () {
                        // 'installed' + existing controller => this is an update, not first install.
                        if (nw.state === 'installed' && navigator.serviceWorker.controller) {
                            showBanner(nw);
                        }
                    });
                });
            }).catch(function (e) { console.log('SW registration failed:', e); });
        });
    })();
</script>
