# LDAP Staff Directory — Interface Design System

## Intent

**Who:** IT/HR admin (configure once) + employees (find colleagues fast).
**What:** Directory lookup — scan name, title, department, find email.
**Feel:** Structured, reliable, corporate — employee badge system meets network directory.

---

## Direction

Domain: corporate badges, org charts, server rooms, ID cards, LDAP tree nodes.

Signature element: **Avatar initials circle** — deterministic per-person color from `crc32(name) % 8`-color palette. No images, works purely from LDAP data.

---

## Palette

### Public frontend tokens (`:root` in `directory.css`)

```css
--ldap-primary-color:    #0073aa;
--ldap-card-bg:          #ffffff;
--ldap-card-border:      #e2e4e7;
--ldap-card-shadow:      0 1px 3px rgba(0,0,0,.06);
--ldap-card-radius:      8px;
--ldap-font-size:        14px;
--ldap-text-color:       #3c434a;
--ldap-muted-color:      #646970;
--ldap-avatar-size:      44px;
--ldap-dept-badge-bg:    rgba(0,115,170,.08);
--ldap-dept-badge-color: #005a87;
```

### Avatar palette (8 corporate colors, deterministic per employee)

```
#4f7df3  #7c5cbf  #0e9b8a
#2e9e4f  #c0392b  #d35400
#1a7bbf  #8e44ad
```

### Admin sidebar card accent stripe colors

| Card | `border-top` |
|---|---|
| `--connection` | `#0073aa` |
| `--cache` | `#00a0d2` |
| `--usage` | `#72777c` |

---

## Depth Strategy

**Border-only** — 1px borders define structure. Shadows whisper (`0 1px 3px rgba(0,0,0,.06)`).
Card hover: shadow grows to `0 4px 14px rgba(0,0,0,.1)` + border tightens.

---

## Spacing

Base unit: **4px**. Scale: 4 / 8 / 10 / 12 / 16 / 20 / 24 / 28 / 32 / 48.

---

## Key Component Patterns

### Employee card

```html
<article class="ldap-staff-card" data-name data-email data-title data-department>
  <div class="ldap-card-avatar" aria-hidden="true" style="--ldap-avatar-bg:#4f7df3">AB</div>
  <h3 class="ldap-name">…</h3>
  <p class="ldap-title">…</p>
  <p class="ldap-department"><span class="ldap-dept-badge">…</span></p>
  <a class="ldap-email" href="mailto:…">…</a>
</article>
```

- Avatar: `44px` circle, `font-size: calc(44px * .38)`, `font-weight: 700`
- Department: tinted pill `border-radius: 10px`, `font-size: .78em`, `font-weight: 500`
- Email: primary color link, `font-size: .88em`, hover `opacity: .82`

### Pagination buttons

Self-contained — **no dependency on WP `.button` class**.

```html
<button class="ldap-btn ldap-prev">…</button>
<button class="ldap-btn ldap-next">…</button>
```

States: default (outline), hover (fill), disabled (0.38 opacity).

### Search input

Icon injected via `.ldap-search-wrap::before` (SVG data URI, no extra markup).
Left padding: `34px` to clear the icon.

### Admin result banners

Icon injected via `.ldap-ed-test-result::before`.
- `.is-success` — green check SVG
- `.is-error` — red circle-x SVG
Left padding: `34px`.

---

## Responsive

| Breakpoint | Behaviour |
|---|---|
| `>1024px` | Sidebar 280px fixed, 3-column grid |
| `≤1024px` | Admin layout stacks; sidebar cards become CSS grid (`auto-fit minmax 220px`) |
| `≤900px` | Directory grid → 2 columns |
| `≤540px` | Directory grid → 1 column |
