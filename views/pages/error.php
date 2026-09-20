<?php
/**
 * views/pages/error.php — Halaman error universal (404/403/500/419)
 * v7.0 — self-contained & defensif (semua variabel opsional)
 */
$code        = (string) ($errorCode ?? '404');
$title       = (string) ($errorTitle ?? 'Halaman Tidak Ditemukan');
$subtitle    = (string) ($errorSubtitle ?? 'Oops! Terjadi kesalahan.');
$description = (string) ($errorDescription ?? '');
$icon        = (string) ($errorIcon ?? 'ph-warning');
$quickLinks  = is_array($quickLinks ?? null) ? $quickLinks : [];
$previousUrl = $previousUrl ?? null;
$homeUrl     = function_exists('url') ? url('') : '/';
?>
<section class="error-page">
    <div class="error-page-orbs" aria-hidden="true">
        <span class="eo eo-1"></span>
        <span class="eo eo-2"></span>
    </div>

    <div class="error-page-card">
        <div class="error-page-icon">
            <i class="ph <?= e($icon) ?>"></i>
        </div>

        <div class="error-page-code"><?= e($code) ?></div>
        <h1 class="error-page-title"><?= e($title) ?></h1>
        <p class="error-page-subtitle"><?= e($subtitle) ?></p>

        <?php if ($description !== ''): ?>
            <p class="error-page-desc"><?= e($description) ?></p>
        <?php endif; ?>

        <div class="error-page-actions">
            <?php if (!empty($previousUrl)): ?>
                <a href="<?= e($previousUrl) ?>" class="ep-btn ghost">
                    <i class="ph ph-arrow-left"></i> Kembali
                </a>
            <?php endif; ?>
            <a href="<?= e($homeUrl) ?>" class="ep-btn primary">
                <i class="ph ph-house"></i> Ke Beranda
            </a>
        </div>

        <?php if (!empty($quickLinks)): ?>
        <div class="error-page-links">
            <span class="ep-links-label">Tujuan populer:</span>
            <div class="ep-links-grid">
                <?php foreach ($quickLinks as $link): ?>
                    <a href="<?= e($link['url'] ?? '#') ?>" class="ep-link">
                        <i class="ph <?= e($link['icon'] ?? 'ph-arrow-right') ?>"></i>
                        <span><?= e($link['label'] ?? '') ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <p class="error-page-meta">Kode <?= e($code) ?> • <?= e(date('d M Y, H:i')) ?></p>
    </div>
</section>

<style>
.error-page {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 100px 24px 60px;
    overflow: hidden;
}
.error-page-orbs { position: absolute; inset: 0; pointer-events: none; }
.eo { position: absolute; border-radius: 50%; filter: blur(90px); opacity: .3; }
.eo-1 { width: 420px; height: 420px; background: #6366f1; top: -120px; left: -120px; }
.eo-2 { width: 360px; height: 360px; background: #22d3ee; bottom: -100px; right: -100px; }

.error-page-card {
    position: relative;
    text-align: center;
    max-width: 560px;
    width: 100%;
    padding: 56px 40px;
    border-radius: 24px;
    background: rgba(255,255,255,.045);
    border: 1px solid rgba(255,255,255,.09);
    backdrop-filter: blur(18px);
    box-shadow: 0 30px 80px rgba(2,6,23,.5);
    animation: ep-in .5s cubic-bezier(.22,1,.36,1) both;
}
@keyframes ep-in {
    from { opacity: 0; transform: translateY(24px) scale(.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.error-page-icon {
    width: 88px; height: 88px;
    margin: 0 auto 20px;
    border-radius: 26px;
    display: grid; place-items: center;
    font-size: 40px; color: #fff;
    background: linear-gradient(135deg, #6366f1, #22d3ee);
    box-shadow: 0 16px 40px rgba(99,102,241,.4);
}
.error-page-code {
    font-size: 56px;
    font-weight: 800;
    letter-spacing: -2px;
    background: linear-gradient(180deg, #ffffff, #c7d2fe);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    line-height: 1;
    margin-bottom: 8px;
}
.error-page-title { font-size: 22px; font-weight: 800; margin-bottom: 8px; }
.error-page-subtitle { color: var(--txt-1, #9aa3c7); font-size: 14px; margin-bottom: 6px; }
.error-page-desc {
    color: var(--txt-1, #9aa3c7);
    font-size: 13px;
    line-height: 1.7;
    max-width: 44ch;
    margin: 0 auto 24px;
}

.error-page-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 24px;
}
.ep-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 20px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    transition: all .25s;
}
.ep-btn.primary {
    background: linear-gradient(135deg, #6366f1, #22d3ee);
    color: #fff;
    box-shadow: 0 10px 26px rgba(99,102,241,.35);
}
.ep-btn.primary:hover { transform: translateY(-2px); }
.ep-btn.ghost {
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.12);
    color: var(--txt-0, #f4f6ff);
}
.ep-btn.ghost:hover { background: rgba(255,255,255,.1); }

.error-page-links { margin-bottom: 20px; }
.ep-links-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: var(--txt-2, #6b7394);
    margin-bottom: 12px;
}
.ep-links-grid {
    display: flex;
    gap: 8px;
    justify-content: center;
    flex-wrap: wrap;
}
.ep-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 99px;
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.1);
    color: var(--txt-1, #9aa3c7);
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all .2s;
}
.ep-link:hover {
    color: #fff;
    border-color: rgba(99,102,241,.5);
    background: rgba(99,102,241,.15);
    transform: translateY(-2px);
}
.ep-link i { font-size: 14px; }

.error-page-meta {
    font-size: 11px;
    color: var(--txt-2, #6b7394);
}
</style>