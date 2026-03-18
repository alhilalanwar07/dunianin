# SVGRepo Icons

Drop SVGRepo blade icon files here using this naming format:

- `resources/views/components/svg/svgrepo/apel.blade.php`
- `resources/views/components/svg/svgrepo/pisang.blade.php`
- etc.

The `<x-svg-icon name="..." />` component is configured to prefer icons in this folder first.
If an icon is not found here, it falls back to `resources/views/components/svg/`.

Tip:
- Paste only the SVG markup.
- Keep `{{ $attributes }}` support if you want dynamic Tailwind sizing/color classes.

Current auto-imported icons (sourced from SVGRepo / SVGRepo-based mirrors):
- `apel`
- `pisang`
- `jeruk`
- `mangga`
- `semangka`
- `kucing`
- `anjing`
- `sapi`
- `ayam`
- `ikan`
- `burung`
- `balon`
- `bintang`
- `hati`
- `gelembung`
- `keranjang`
- `gembok`
- `centang`
- `mahkota`

Notes:
- Direct requests to svgrepo.com are currently rate-limited from this environment.
- Some names use closest-available SVGRepo themed matches from mirrors when exact keyword files were unavailable.
