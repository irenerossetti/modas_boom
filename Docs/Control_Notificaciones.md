# Control de Notificaciones - QR Proxy API

## Endpoints

- GET /admin/notificaciones/qr
  - Query param: `format` (optional) -> `base64` | `dataurl` | `svg`
  - Behavior:
    - If the upstream service returns a JSON object and contains a `base64` property, that is preferred and normalized.
    - If the upstream returns a `data:` URL string, the controller extracts the base64 portion and returns `qr` (raw base64) and `dataUrl`.
    - If the upstream returns an `svg` string, the controller returns `{ svg: "..." }`.
    - If the `format=base64` param is used, the controller always returns a simplified JSON like `{ qr: "<base64>" }` when possible.
    - If the `format=image` param is used, the controller returns the decoded PNG binary via `Content-Type: image/png` and does not use, store, or broadcast the base64 in any other part of the system (read-only mode).
    - The controller adds `dataUrl` (`data:image/png;base64,...`) and `base64` fields to object responses when possible; `qr` field will contain the normalized raw base64.

- POST /admin/notificaciones/generate-qr
  - Forwards request to the upstream `generate-qr` and broadcasts a `QrUpdated` event with the normalized base64 value by default.
  - If called with `?format=image` the endpoint returns the decoded PNG binary and does **not** dispatch a broadcast event (read-only mode).

## Normalization rules

The controller normalizes QR responses coming in various formats:
- Raw base64 (e.g., `iVBORw0KGgo=`) -> clean base64 returned unchanged.
- `data:image/png;base64,<base64>` -> extracted raw base64 (`qr`) returned and `dataUrl` header added.
- Comma or space separated base64 chunks (e.g., `iVB,ORw0K Gg==`) -> concatenated and returned normalized.
- JSON responses with `base64` or `qr` fields -> those values are normalized and `base64`, `qr`, `dataUrl` are set in response object.
- `svg` strings -> returned unchanged in `{ svg: "..." }`.

## Client UI Recommendation

- For the admin UI, call: `/admin/notificaciones/qr?format=base64` to receive a `qr` field with raw base64 suitable for rendering an `<img src="data:image/png;base64,{qr}">`.
- The UI should gracefully handle `svg` responses by rendering it directly on the page.
