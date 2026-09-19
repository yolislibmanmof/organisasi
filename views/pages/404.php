<!-- File: views/pages/404.php (TAHAP 5.7) -->
<section style="min-height:70vh;display:grid;place-items:center;padding:60px 24px;">
    <div class="glass-card reveal" style="max-width:560px;width:100%;padding:60px 44px;text-align:center;">
        <div style="width:120px;height:120px;margin:0 auto 24px;border-radius:36px;background:linear-gradient(135deg,rgba(99,102,241,.2),rgba(34,211,238,.15));display:grid;place-items:center;position:relative;">
            <i class="ph ph-magnifying-glass" style="font-size:56px;color:var(--acc);"></i>
            <span style="position:absolute;inset:-8px;border-radius:44px;background:conic-gradient(from 0deg,transparent,rgba(99,102,241,.4),transparent);filter:blur(14px);z-index:-1;animation:spin 4s linear infinite;"></span>
        </div>
        <div style="font-size:11px;font-weight:800;letter-spacing:2px;color:var(--acc);margin-bottom:10px;text-transform:uppercase;">Error 404</div>
        <h1 style="font-size:clamp(26px,4vw,36px);font-weight:800;letter-spacing:-.8px;margin-bottom:14px;line-height:1.15;">
            Halaman <em style="font-family:'Instrument Serif',serif;font-style:italic;background:linear-gradient(135deg,#a5b4fc,#67e8f9);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;">tidak ditemukan</em>
        </h1>
        <p style="color:var(--txt-1);font-size:14.5px;line-height:1.7;max-width:40ch;margin:0 auto 28px;">
            Halaman yang Anda tuju mungkin telah dipindahkan atau tidak lagi tersedia. Mari kembali ke beranda.
        </p>
        <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
            <a href="<?= url('') ?>" class="btn btn-primary">
                <i class="ph ph-house"></i><span class="btn-text">Kembali ke Beranda</span>
            </a>
            <a href="javascript:history.back()" class="btn btn-ghost">
                <i class="ph ph-arrow-left"></i><span class="btn-text">Halaman Sebelumnya</span>
            </a>
        </div>
    </div>
</section>