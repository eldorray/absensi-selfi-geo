# Welcome Material 3 Redesign

## Objective

Redesign `resources/views/welcome.blade.php` as a concise Material Design 3 gateway for MI Daarul Hikmah. The page must direct guests to sign in and authenticated users to the correct dashboard without changing application routes or authentication behavior.

## Design Read

A gateway for a school employee attendance application, using an expressive yet institutional Material You language.

- ENERGY 2
- RHYTHM 2
- MOTION 1
- Selected composition: Tonal Gateway
- Antislop mode: during planning and implementation

## Content and Information Architecture

The page remains a single-screen gateway with this order:

1. A compact top app bar containing the AbsenKu icon and name, MI Daarul Hikmah identity, and theme toggle.
2. One organic `primary-container` hero as the visual focal point.
3. The heading “Presensi yang siap saat Anda tiba.”
4. A short explanation that swafoto and location are checked when attendance begins.
5. Two concise capability labels for swafoto and location verification. They describe product capabilities, not live device readiness.
6. One primary action:
   - Guest: “Masuk ke AbsenKu”, linking to `route('login')`.
   - Authenticated administrator: “Buka Dashboard”, linking to `route('admin.dashboard')`.
   - Other authenticated user: “Buka Dashboard”, linking to `route('attendance.dashboard')`.
7. A minimal organization and year footer.

Remove the fake phone shell, simulated camera viewfinder, dynamic island, speaker, decorative grid, animated blobs, glass surfaces, perpetual pulse effects, and redundant highlight grid.

## Material 3 Visual System

### Color

Use a complete green Material 3 tonal palette aligned with the existing attendance identity:

- Primary green for the main action and important icon treatment.
- Primary container for the organic hero.
- Secondary container for supporting capability labels.
- Warm neutral surface levels for page hierarchy.
- Error tokens included for completeness, although this static gateway does not currently display an error state.

The page is light-first for readability and an open institutional character. A complete dark palette remains available through the theme toggle. Active color usage is limited to green, neutrals, and an optional amber accent only where functionally justified.

### Typography

Use Plus Jakarta Sans for readable Indonesian interface copy. Hierarchy comes from size and weight rather than wide-tracked uppercase labels. Headings are bold and compact; body copy remains comfortable on narrow screens.

### Shape and Elevation

- Hero: a large asymmetric organic container with one reduced corner as the repeated identity motif.
- Supporting labels: medium-radius tonal containers.
- Primary action: Material 3 full-rounded button.
- Theme toggle: circular icon button.
- Most surfaces remain flat. Elevation is reserved for the main action or focal container when needed to communicate priority.

### Motion

MOTION 1 permits only purposeful state transitions:

- Theme color transition.
- Hover, focus, and pressed feedback.
- No entrance choreography, floating decoration, pulsing rings, or endless animation.
- Respect `prefers-reduced-motion`.

## Responsive Behavior

- Mobile: full-width single-column composition with safe viewport height and no horizontal overflow.
- Desktop: centered content with a deliberate maximum width, not a fake phone frame.
- All controls must provide at least a 44 by 44 pixel touch target.
- Content must remain usable at narrow mobile widths and enlarged text settings.

## Accessibility

- Light and dark token pairs must meet WCAG AA: 4.5:1 for normal text and 3:1 for large text.
- Every interactive element receives a visible `:focus-visible` indicator.
- The theme toggle has a descriptive label that reflects the action available.
- Decorative SVGs are hidden from assistive technology; meaningful icons have accessible context.
- Navigation order follows visual order.
- Existing zoom restrictions in the viewport metadata must be removed so users can enlarge content.

## Behavior and Integration

Preserve:

- Existing named routes and role-dependent destination logic.
- Authentication-aware guest and user content.
- PWA manifest, icons, metadata, and Vite assets.
- Theme preference persistence in `localStorage`.

Theme initialization must run safely before paint without accessing `document.body` before it exists. It should use a root-level theme attribute or class, update `meta[name="theme-color"]`, and synchronize the theme-toggle accessible label.

No new dependencies, routes, controllers, or global CSS are required. Styles remain scoped to the standalone welcome view so admin and employee surfaces are unaffected.

## Verification

1. Add or update a Pest feature test for guest and authenticated rendering, CTA destinations, and key accessibility markup.
2. Run the focused Pest test.
3. Run `vendor/bin/pint --dirty`.
4. Run `php artisan view:cache`.
5. Run `npm run build`.
6. Programmatically verify contrast ratios for normal-text token pairs in both themes.
7. Render and inspect mobile and desktop screenshots in light and dark themes.
8. Exercise login/dashboard links and theme toggle with keyboard input.
9. Confirm no JavaScript console errors and no horizontal overflow.

## Decision Rationale

- Green tonal containers retain the established attendance and school identity.
- The organic hero supplies Material You character while serving content hierarchy.
- A single primary action matches the gateway’s only user decision.
- Solid tonal surfaces improve legibility and hierarchy compared with glassmorphism.
- Low motion suits a repetitive daily utility.
- Removing the phone mockup lets the page behave like the application instead of presenting a picture of it.
