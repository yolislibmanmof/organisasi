<?php
// File: app/Controllers/ContentController.php (FINAL - TAHAP 5.6)
declare(strict_types=1);

namespace Controllers;

use Core\Session;
use Core\View;
use Middleware\AdminOnly;
use Middleware\Auth;
use Models\Gallery;
use Models\Officer;
use Models\Testimonial;

class ContentController {
    private const TYPES = ['officers', 'testimonials', 'galleries'];

    public function manage(): void {
        Auth::handle();
        AdminOnly::handle();
        View::render('pages/content', [
            'title'  => 'Konten Situs',
            'user'   => Session::get('user'),
            'counts' => [
                'officers'     => count(Officer::all()),
                'testimonials' => count(Testimonial::all()),
                'galleries'    => count(Gallery::all()),
            ],
        ], 'layouts/app');
    }

    public function api(string $type): void {
        Auth::handle();
        AdminOnly::handle();
        if (!in_array($type, self::TYPES, true)) json(['ok' => false, 'message' => 'Tipe tidak valid.'], 404);
        json(['data' => match ($type) {
            'officers'     => Officer::all(),
            'testimonials' => Testimonial::all(),
            'galleries'    => Gallery::all(),
        }]);
    }

    public function store(): void {
        Auth::handle();
        AdminOnly::handle();
        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);

        $type = (string) ($_POST['type'] ?? '');
        try {
            if ($type === 'officers') {
                $d = $this->validateOfficer();
                if (isset($d['errors'])) json(['ok' => false, 'errors' => $d['errors']], 422);
                $up = $this->upload($_FILES['photo'] ?? null, 'officers');
                if (isset($up['error'])) json(['ok' => false, 'message' => $up['error']], 422);
                $d['photo'] = $up['name'] ?? null;
                Officer::create($d);
            } elseif ($type === 'testimonials') {
                $d = $this->validateTestimonial();
                if (isset($d['errors'])) json(['ok' => false, 'errors' => $d['errors']], 422);
                Testimonial::create($d);
            } elseif ($type === 'galleries') {
                $d = $this->validateGallery();
                if (isset($d['errors'])) json(['ok' => false, 'errors' => $d['errors']], 422);
                $up = $this->upload($_FILES['image'] ?? null, 'galleries', true);
                if (isset($up['error'])) json(['ok' => false, 'message' => $up['error']], 422);
                $d['image'] = $up['name'];
                Gallery::create($d);
            } else {
                json(['ok' => false, 'message' => 'Tipe tidak valid.'], 422);
            }
            json(['ok' => true, 'message' => 'Konten berhasil disimpan.']);
        } catch (\Throwable $e) {
            json(['ok' => false, 'message' => 'Gagal menyimpan konten: ' . $e->getMessage()], 500);
        }
    }

    public function update(string $id): void {
        Auth::handle();
        AdminOnly::handle();
        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
        $id   = (int) $id;
        $type = (string) ($_POST['type'] ?? '');

        try {
            if ($type === 'officers') {
                $old = Officer::find($id);
                if (!$old) json(['ok' => false, 'message' => 'Data tidak ditemukan.'], 404);
                $d = $this->validateOfficer();
                if (isset($d['errors'])) json(['ok' => false, 'errors' => $d['errors']], 422);
                $up = $this->upload($_FILES['photo'] ?? null, 'officers');
                if (isset($up['error'])) json(['ok' => false, 'message' => $up['error']], 422);
                $d['photo'] = $up['name'] ?? $old['photo'];
                if (!empty($up['name']) && !empty($old['photo'])) $this->unlinkUpload('officers', $old['photo']);
                Officer::update($id, $d);
            } elseif ($type === 'testimonials') {
                if (!Testimonial::find($id)) json(['ok' => false, 'message' => 'Data tidak ditemukan.'], 404);
                $d = $this->validateTestimonial();
                if (isset($d['errors'])) json(['ok' => false, 'errors' => $d['errors']], 422);
                Testimonial::update($id, $d);
            } elseif ($type === 'galleries') {
                $old = Gallery::find($id);
                if (!$old) json(['ok' => false, 'message' => 'Data tidak ditemukan.'], 404);
                $d = $this->validateGallery(true);
                if (isset($d['errors'])) json(['ok' => false, 'errors' => $d['errors']], 422);
                $up = $this->upload($_FILES['image'] ?? null, 'galleries');
                if (isset($up['error'])) json(['ok' => false, 'message' => $up['error']], 422);
                $d['image'] = $up['name'] ?? $old['image'];
                if (!empty($up['name'])) $this->unlinkUpload('galleries', $old['image']);
                Gallery::update($id, $d);
            } else {
                json(['ok' => false, 'message' => 'Tipe tidak valid.'], 422);
            }
            json(['ok' => true, 'message' => 'Perubahan konten telah disimpan.']);
        } catch (\Throwable $e) {
            json(['ok' => false, 'message' => 'Gagal menyimpan perubahan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(string $id): void {
        Auth::handle();
        AdminOnly::handle();
        if (!csrf_verify($_POST[CSRF_TOKEN_NAME] ?? null)) json(['ok' => false, 'message' => 'Sesi tidak valid.'], 419);
        $id   = (int) $id;
        $type = (string) ($_POST['type'] ?? '');

        if ($type === 'officers') {
            $old = Officer::find($id);
            if ($old && !empty($old['photo'])) $this->unlinkUpload('officers', $old['photo']);
            Officer::delete($id);
        } elseif ($type === 'testimonials') {
            Testimonial::delete($id);
        } elseif ($type === 'galleries') {
            $old = Gallery::find($id);
            if ($old && !empty($old['image'])) $this->unlinkUpload('galleries', $old['image']);
            Gallery::delete($id);
        } else {
            json(['ok' => false, 'message' => 'Tipe tidak valid.'], 422);
        }
        json(['ok' => true, 'message' => 'Konten telah dihapus.']);
    }

    /* ---------- Validasi ---------- */
    private function validateOfficer(): array {
        $errors = [];
        $name   = trim((string) ($_POST['full_name'] ?? ''));
        $pos    = trim((string) ($_POST['position'] ?? ''));
        if (mb_strlen($name) < 3) $errors['full_name'] = 'Nama minimal 3 karakter.';
        if ($pos === '')          $errors['position']  = 'Jabatan wajib diisi.';
        return $errors !== [] ? ['errors' => $errors]
            : [
                'full_name'  => $name,
                'position'   => $pos,
                'sort_order' => (int) ($_POST['sort_order'] ?? 0),
                'bio'        => trim((string) ($_POST['bio'] ?? '')),
            ];
    }

    private function validateTestimonial(): array {
        $errors = [];
        $name   = trim((string) ($_POST['name'] ?? ''));
        $quote  = trim((string) ($_POST['quote'] ?? ''));
        if (mb_strlen($name) < 3)   $errors['name']  = 'Nama minimal 3 karakter.';
        if (mb_strlen($quote) < 10) $errors['quote'] = 'Testimoni minimal 10 karakter.';
        return $errors !== [] ? ['errors' => $errors]
            : ['name' => $name, 'role' => trim((string) ($_POST['role'] ?? '')), 'quote' => $quote];
    }

    private function validateGallery(bool $allowEmpty = false): array {
        $title = trim((string) ($_POST['title'] ?? ''));
        if ($title === '') return ['errors' => ['title' => 'Judul foto wajib diisi.']];
        return [
            'title'      => $title,
            'event_date' => trim((string) ($_POST['event_date'] ?? '')),
            'location'   => trim((string) ($_POST['location'] ?? '')),
        ];
    }

    /* ---------- Unggah berkas (batas 10 MB) ---------- */
    private function upload(?array $file, string $folder, bool $required = false): array {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return $required ? ['error' => 'Berkas wajib diunggah.'] : [];
        }
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            return ['error' => 'Berkas melebihi batas unggah server. Periksa php.ini (upload_max_filesize & post_max_size).'];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) return ['error' => 'Gagal mengunggah berkas.'];
        if ($file['size'] > 10 * 1024 * 1024) return ['error' => 'Ukuran maksimal 10 MB.'];
        $info = @getimagesize($file['tmp_name']);
        if ($info === false) return ['error' => 'Berkas harus berupa gambar.'];
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($info['mime'], $allowed, true)) return ['error' => 'Format didukung: JPG, PNG, WEBP.'];

        $ext  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$info['mime']];
        $name = $folder . '-' . bin2hex(random_bytes(8)) . '.' . $ext;
        $dir  = dirname(__DIR__, 2) . '/public/assets/uploads/' . $folder . '/';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        if (!move_uploaded_file($file['tmp_name'], $dir . $name)) return ['error' => 'Gagal menyimpan berkas.'];
        return ['name' => $name];
    }

    private function unlinkUpload(string $folder, string $name): void {
        $path = dirname(__DIR__, 2) . '/public/assets/uploads/' . $folder . '/' . $name;
        if (is_file($path)) @unlink($path);
    }
}