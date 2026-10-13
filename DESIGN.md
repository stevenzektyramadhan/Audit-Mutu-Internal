# SPMI Assignment Create Design Contract

This contract is the visual source of truth for the SPMI assignment-create page in the existing CodeIgniter 3 dashboard. It is a scoped extension of the current AMI shell, not a new brand or a replacement design system. It covers the page shell, assignment form, standard selection table, and submit/cancel actions only.

## 1. Product surface and design intent

- Audience: LPMPI management users creating a draft SPMI audit assignment.
- Intent: make the required setup sequence clear—choose the cycle, choose the auditee/auditor context, select standards, then create the assignment.
- Visual direction: navy sidebar, light-gray content canvas, white surfaces, and the existing bright blue for active navigation, links, focus, and primary actions.
- Preserve the existing dashboard shell and Bootstrap 4 conventions. Do not introduce a new font, gradient brand, global component library, or unrelated token.
- The page is a form workflow. It must not look like a dashboard metric page or a dense administrative spreadsheet without hierarchy.

## 2. Existing shell contract

Use the existing layout primitives and classes from `application/views/layouts/header.php` and `sidebar.php`:

- Root: `.ami-app` containing `.ami-sidebar` and `.ami-main`.
- Sidebar: `.ami-sidebar` is `248px` wide on desktop, uses `var(--ami-sidebar)` (`#172033`), grouped navigation labels, and `.ami-nav-link.active` for the current destination. Keep `aria-current="page"` on the active submenu link.
- Main chrome: `.ami-topbar` contains the page title/subtitle and account controls; page content belongs inside `.ami-content`.
- Content canvas: `var(--ami-bg)` (`#f3f5f8`); cards and panels use `var(--ami-panel)` (`#ffffff`).
- Shell text: `var(--ami-text)` (`#202733`) for primary content and `var(--ami-muted)` (`#667085`) for supporting text.
- Existing spacing tokens: `var(--ami-space-sm)` = `8px`, `var(--ami-space-md)` = `16px`, and `var(--ami-space-lg)` = `24px`. All new spacing must use the 4px grid; use 4, 8, 12, 16, 20, 24, and 32px only.
- Existing shape tokens: `var(--ami-radius-sm)` = `7px`; use it for cards, controls, and buttons rather than inventing a new radius.
- Single scroll owner: the document viewport (`body`/the page) owns vertical scrolling. Do not set a page-level `overflow-y: auto` on `.ami-main`, `.ami-content`, the form card, or the table card. A table may use a local horizontal overflow wrapper only when its columns cannot fit.

## 3. Page composition

The content area follows this order:

1. A compact back link using the existing scoped pattern (`.audits-back-link`), with visible text and an icon that is decorative.
2. Page heading and one-sentence subtitle. The heading follows `.ami-page-title` hierarchy; supporting copy uses `var(--ami-muted)`.
3. A short info callout explaining that the assignment is created as a draft and that selected standards are snapshotted into assignment items. Use `role="note"` or `role="status"` as appropriate; do not use an alert role for normal guidance.
4. One form card containing the assignment context fields and the standard-selection table.
5. A bottom action bar inside the form card for cancel and create actions.

Keep the content readable at a maximum width of approximately `960px` to `1120px`; do not force the form to full viewport width. Use the existing `.ami-content` padding and a page-local wrapper rather than changing global layout rules.

## 4. Tokens and type

Prefer the existing global tokens below. Page-local aliases are allowed only when needed to scope selectors under `#audits-root`; aliases must map directly to these values.

| Purpose | Existing token/value | Contract use |
|---|---|---|
| Page background | `var(--ami-bg)` / `#f3f5f8` | Content canvas only |
| Surface | `var(--ami-panel)` / `#ffffff` | Form card, callout, table surface |
| Sidebar | `var(--ami-sidebar)` / `#172033` | Existing navigation only |
| Text | `var(--ami-text)` / `#202733` | Headings, labels, table content |
| Muted text | `var(--ami-muted)` / `#667085` | Help, metadata, secondary labels |
| Border | `var(--ami-border)` / `#d7dde6` | Card, field, divider, table rules |
| Primary blue | `var(--ami-blue)` / `#185fa5` | Links, active state, primary CTA |
| Link soft | `var(--ami-link-soft)` | Focus ring and selected soft background |
| Hover blue | `var(--audits-blue-hover)` / `#124e89` | Primary CTA/link hover |

- Body typography remains the shell stack: `-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif`, base `14px`.
- Page title/section headings use existing shell or Bootstrap heading hierarchy; do not add arbitrary display sizes. Section labels may use `12px` to `14px`, semibold/bold.
- Body and table content use `14px`; help text and metadata use `12px` to `13px` with at least `1.4` line-height.
- Text/background combinations must meet WCAG AA: at least `4.5:1` for normal text and `3:1` for large text and non-text controls. Never communicate selection, error, or disabled state by color alone.

## 5. Required primitives

### 5.1 Form card

- Use a white surface with `1px` `var(--ami-border)`, `var(--ami-radius-sm)`, and the existing panel treatment; avoid a second nested card for every field.
- Card padding is `24px` on desktop and `16px` on small screens.
- Group fields by purpose with a clear section heading and a `1px` divider when needed. Field groups use `16px` vertical rhythm; label-to-control spacing is `8px`.
- Labels are visible, associated with their controls, and semibold. Required fields include text such as “wajib”; do not rely on a red asterisk alone.
- Controls follow the existing `.tw-field`/Bootstrap form-control pattern: full width, white background, border, `var(--ami-radius-sm)` or the existing scoped rounded control, and a visible blue focus ring.
- Help/error text sits immediately below its control. Errors use the existing danger/rose semantic family and must be referenced with `aria-describedby`.

### 5.2 Info callout

- Use a low-emphasis surface based on `var(--ami-link-soft)` with a `1px` border derived from `var(--ami-blue)` and readable `var(--ami-text)` copy.
- Include a decorative information icon plus a short title or lead sentence; the icon must have `aria-hidden="true"`.
- Keep padding at `12px 16px`, gap `8px`, and line-height at least `1.4`.
- This is guidance, not an error. Do not use a dismissible toast or an assertive live region for static page instructions.

### 5.3 Standard table

- Use a semantic `<table>` with `<caption>` or an adjacent labelled heading, `<thead>`, `<tbody>`, and scoped `<th>` cells.
- Header cells use the existing light table tone (`#f7f9fc` where the local pattern already uses it), muted uppercase metadata styling, and `1px` bottom rules.
- Body cells use `var(--ami-text)`, `var(--ami-border)`, and `12px`/`13px` horizontal padding consistent with `.users-table`.
- Row hover is supplementary only; it must not be the only selection cue.
- Keep the standard identity readable: code/title is the primary column; the assigned auditor and selection state are secondary.
- On narrow screens, prefer the existing responsive table pattern (stacked labelled cells) when the row remains understandable. Otherwise wrap only the table horizontally in a labelled container; the wrapper is the only local horizontal scroll owner.

### 5.4 Selection switch

- Use a native checkbox or radio input with a visible text label; the control must remain keyboard-operable and retain a usable native focus target.
- A switch-like visual may be composed around the native control, but it must not hide or remove the input from the accessibility tree.
- Selected state uses `var(--ami-blue)` plus a text/state cue such as “Dipilih”; unselected state uses the panel and border tokens. The selected row may receive `var(--ami-link-soft)` but must also expose `aria-selected` only when the table interaction pattern supports it.
- Minimum interactive target is `44px` in both dimensions, including the label/control hit area. Do not require precision clicking.
- If the page supports “select all”, its label and mixed state must be exposed to assistive technology; selecting all must not silently select disabled or unavailable indicators.

### 5.5 Action bar

- Place actions after the final field/table content, separated by a top border or `16px` spacing; use an 8px gap between buttons.
- Primary create action uses `.tw-button-primary`/the existing blue button pattern. It is the only visually dominant action.
- Cancel uses `.tw-button-secondary` or a plain back link when it navigates away without mutating data.
- Actions are right-aligned on wide screens and become full-width, stacked controls on small screens. Preserve DOM order as Cancel then Create only if that matches the form’s safe keyboard flow; the primary action must remain easy to find and clearly named.

## 6. States and behavior

- Default: white controls, `var(--ami-border)` borders, normal text, and no decorative emphasis beyond hierarchy.
- Hover: darken blue to `var(--audits-blue-hover)` for primary actions; secondary controls may gain blue border/text or `var(--ami-link-soft)` background. Hover must not move content or be the only state signal.
- Focus: every link, button, select, checkbox/radio, and switch has a `:focus-visible` treatment. Use the existing blue focus-ring convention (`0 0 0 3px var(--audits-blue-soft)` or `var(--ami-link-soft)`) plus a visible outline where needed. Never remove the browser focus indicator without replacing it.
- Selected: expose the state in the control itself and visually mark the table row/control using blue plus a non-color cue.
- Disabled: use native `disabled` semantics where unavailable; retain readable text, remove hover lift, and use `cursor: not-allowed`. Disabled controls must not be mistaken for selected or successful controls.
- Error: show an inline message adjacent to the invalid control, identify the field, preserve the user’s valid values, and move focus to the first invalid field after a failed submit when implemented by the existing form behavior.
- Loading/submitting: disable duplicate submission, keep the button label understandable (for example, “Membuat penugasan…”), and do not hide the form or rely on a spinner alone.
- Empty table: show a concise explanation and the next action needed; do not render a blank table that looks like a successful zero-selection state.
- Reduced motion: respect `prefers-reduced-motion: reduce`; transitions must be removed or shortened without affecting state communication.

## 7. Accessibility and responsive contract

- Use landmarks already supplied by the shell; the page-local root must have one clear heading and must not create a competing `main` landmark.
- Use Indonesian labels and instructions consistent with the existing application. Every input has a programmatic label; every error/help relationship is programmatic.
- Keyboard order follows visual order. Users can reach, inspect, select, deselect, cancel, and submit without a pointer. Do not trap focus in the table or callout.
- Use `aria-live="polite"` only for asynchronous result/status updates. Do not add redundant ARIA to native controls.
- Desktop breakpoint: `992px` (`lg`), matching the shell’s sidebar toggle behavior. At or above it, the sidebar is persistent and the action bar may be horizontal.
- Below `992px`, the existing sidebar becomes an overlay drawer; opening it locks body scrolling via `.ami-sidebar-lock`. The page content remains the document scroll owner after the drawer closes.
- At `768px` and below, matching existing page CSS, collapse multi-column form/table arrangements to one column, reduce card padding to `16px`, let action buttons fill available width, and keep table content readable through stacked cells or the local horizontal wrapper.
- At `576px` and below, avoid fixed-width controls and clipped headings; allow labels, buttons, and table values to wrap. Maintain `44px` minimum touch targets.
- Do not add a second vertical scrollbar to compensate for responsive layout. Long pages scroll in the document viewport.

## 8. Implementation boundary and acceptance checklist

This contract authorizes only the assignment-create page’s scoped markup/classes/styles in the existing SPMI audit surface. It does not authorize edits to global layouts, routes, controllers, services, models, migrations, dependencies, or unrelated AMI legacy UI.

Before implementation is considered complete:

- [ ] The page uses the existing `.ami-*` shell and scoped `#audits-root` patterns rather than inventing global names or tokens.
- [ ] Sidebar, active link, page title, light-gray canvas, white form/table surfaces, and bright-blue CTA match the existing shell.
- [ ] Form card, info callout, semantic table, selection switch, and action bar are present with the states defined above.
- [ ] Spacing follows the 4px grid; no arbitrary visual magic numbers are introduced.
- [ ] The document viewport is the sole vertical scroll owner; only a necessary table wrapper may scroll horizontally.
- [ ] Focus, error, disabled, loading, selected, empty, and reduced-motion states are defined and testable.
- [ ] WCAG AA contrast, labels, keyboard access, focus visibility, target size, and responsive behavior are preserved.
- [ ] No unrelated component, brand direction, greenfield research log, or global layout change is added.
