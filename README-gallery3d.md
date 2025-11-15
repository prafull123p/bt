3D Gallery (gallery3d.php)

Overview

- `gallery3d.php` is a creative 3D gallery front-end that reads images from the `gallery` table.
- It loads the first page server-side and uses `api/gallery.php` to load additional pages (infinite scroll / Load more).
- Images are lazy-loaded using the `loading="lazy"` attribute.
- Global 3D intensity is configurable from the admin panel and per-image `effect_strength` can override it.

Admin (CRUD)

- Manage images via `admin_gallery_upload.php`.
- New fields added:
  - `color_tag` (string) — optional color identifier (e.g., `#ff4080` or `pink`).
  - `display_order` (int) — lower numbers show first.
  - `featured` (boolean) — mark an image as featured.
  - `effect_strength` (int) — per-image intensity; 0 uses global setting.
- On first use, the admin page will attempt to add the new columns to the `gallery` table if they are missing (requires DB ALTER privileges).

Settings

- In `admin_gallery_upload.php` there is a "Gallery Settings" section that lets you set the global 3D intensity (0-40).
- Settings are saved to `tmp/gallery_settings.json`.

API

- `api/gallery.php?page=1&per_page=12` returns JSON with fields `{ page, per_page, images:[], has_more }`.

Testing

- A small integration test is provided at `tests/test_gallery_api.php`.
- Run it from the project root:

```bash
php tests/test_gallery_api.php
```

Notes & Next Steps

- The admin uploader keeps the existing `uploads/gallery/` folder for images. Ensure PHP can write to this folder.
- If your DB user lacks ALTER privileges, the automatic column creation will fail; manually add columns:

ALTER TABLE gallery
  ADD COLUMN color_tag VARCHAR(50),
  ADD COLUMN display_order INT DEFAULT 9999,
  ADD COLUMN featured TINYINT(1) DEFAULT 0,
  ADD COLUMN effect_strength INT DEFAULT 0;

- Consider adding server-side caching for API results and image optimization on upload (resize / WebP conversion).

Enjoy the new 3D gallery!