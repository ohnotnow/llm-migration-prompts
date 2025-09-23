# Framework‑Agnostic Migration Playbook — Bulma/Vue ➜ Livewire v3 + FluxUI (Zero‑Vue, Zero‑Bulma)

This is the **core playbook** for migrating legacy Laravel apps from Bulma + Vue to **Livewire v3 + FluxUI** with Tailwind. It captures the principles, process, preferences, and edge‑case handling that proved successful, without app‑specific details. Use it across *any* application. The target state has **no Vue** and **no Bulma** remaining.

---

## 1) North Star

* **Outcome:** Server‑driven UI with Livewire v3 and FluxUI components; Tailwind for utility styling; accessible, responsive, and consistent.
* **Non‑Goals:** No partial rewrites that leave Bulma/Vue mixed in. No app‑specific hacks. No editing built assets in `public/`.
* **Success Criteria:** Feature parity (or intentional improvements), clean diffs, deterministic tests, and consistent UX patterns.

---

## 2) Migration Pillars (Principles)

1. **Zero Vue/Bulma:** Remove, don’t wrap. Replace Vue behaviour with Livewire (or Alpine for tiny UI affordances) and Bulma classes with Flux + Tailwind.
2. **Single Source of Truth:** Prefer Eloquent relationships and server state; avoid duplicating state in JS.
3. **Conservative Scope:** Change only what the migration requires. Don’t opportunistically refactor unrelated code.
4. **Consistency Over Cleverness:** Reuse established patterns (layout, spacing, forms, tables). Future maintainers > short‑term novelty.
5. **Fail Fast in Code, Fix Fixtures:** Don’t paper over domain assumptions with null‑guards; make tests/fixtures reflect the rules.
6. **Accessible by Default:** Labels, roles, focus order, semantics; don’t regress a11y while swapping frameworks.
7. **Deterministic Tests:** Prefer Livewire/component tests for logic and HTTP GET for visibility. Assert copy/behaviour over brittle HTML structure.

---

## 3) Target Architecture

* **Layouts:** Blade components (single root element), Flux page chrome, Tailwind utilities for spacing and dark mode.
* **Pages:** Prefer **full‑page Livewire components** for complex flows. Only create child components when reuse is proven.
* **Interactions:** Livewire actions/state; Alpine for micro‑interactions (collapse, simple toggles) if Livewire round‑trips are overkill.
* **Styling:** Flux components first, Tailwind utilities second. Avoid custom CSS unless necessary.

---

## 4) Canonical UI Patterns

### Forms

* Use **Flux shorthand** where possible: `<flux:input name="title" label="Title" />`.
* **Date inputs:** Replace Pikaday/legacy widgets with `<flux:date-picker>`; values in ISO `Y-m-d`.
* **Selects:** Use `<flux:select>` with `<flux:select.option>` only. No raw `<option>` tags. Use modern bindings (`:selected`, `:disabled`, `:required`).
* **Types matter:** If an input is an email, set `type="email"`. Prefer semantic types for built‑in validation and a11y.
* **Files:** `<flux:input type="file" ... />` with explicit `accept` where applicable.
* **Spacing:** Group fields inside a vertical rhythm wrapper like `class="flex-1 space-y-6"`; avoid manual margins around `<flux:separator />` inside such wrappers.

### Tables & Lists

* Server‑driven pagination/filters via Livewire; avoid client‑only search/sort clones.
* Give each row a stable `wire:key` to keep DOM diffs predictable.
* Keep headers legible; if rotated or segmented, ensure alignment props and readable labels.

### Buttons, Callouts, Links

* Prefer Flux variants (`primary|outline|ghost|danger`) and use them consistently.
* Tailwind reset removes native cursors; add `cursor-pointer` on custom clickable containers.
* Use `<flux:link>` or button `href` for navigational actions; reserve submit buttons for form submits.

### Layout & Responsive

* Width constraints (`max-w-xl/2xl/4xl`) for readable line lengths.
* Grids over columns: e.g., `grid grid-cols-1 lg:grid-cols-2 gap-8`.
* Replace Bulma responsive helpers with Tailwind equivalents (e.g., `hidden sm:block`).
* Dark mode friendly text colours (e.g., `text-zinc-600 dark:text-zinc-400`).

---

## 5) Behavioural Migration (Vue ➜ Livewire)

* **State & Events:** Move business logic to Livewire actions (`save`, `delete`, etc.). Avoid ad‑hoc JS event buses.
* **Validation:** Livewire validation handles user input; type properties as `int|string` etc. where needed so PHP doesn’t throw before validation.
* **Feedback:** Replace flash‑based success UI with Flux toasts or callouts.
* **Routing:** Point routes to Livewire classes for page screens and use `$this->redirectRoute(...)` in actions.
* **Alpine:** Use for instant UI affordances only (collapse, toggles), not for data truth.

---

## 6) Decision Frameworks (Make the Same Choice Every Time)

**When to split components?**

* Split when: clear reuse across pages, measurable performance gain, or boundary of concern (e.g., table cell rendering widgets).
* Don’t split for: speculative reuse, tiny files, or to emulate Vue component granularity.

**When to keep client‑side JS?**

* Use Alpine for micro‑interactions that don’t need server state.
* Use Livewire when interaction changes server state or relies on model data.

**Typing strategy for bound props**

* Be lenient (`int|string`) at property boundaries; let validation coerce/guard. Be strict in domain/service layers.

**Null‑guard policy**

* Don’t add guards to mask broken assumptions. Fix the data setup (fixtures/seeds/tests) or enforce via queries.

---

## 7) Data & Querying

* Always prefer Eloquent relationships (`with`, `whereHas`) to manually joined arrays.
* Derive computed values in models/services; avoid passing oversized JSON blobs to the view.
* Eager‑load relations for lists to prevent N+1 queries.

---

## 8) Accessibility & UX

* Every field needs a programmatic label. Use Flux `label` props or explicit `<flux:label>`.
* Keep focus states visible; don’t remove outlines unless replaced with an accessible alternative.
* Respect reduced motion preferences if adding animations.
* Ensure keyboard operability for menus, modals, and segmented controls.

---

## 9) Testing Strategy (Portable)

* **Page visibility:** HTTP GET route tests (feature) that assert key copy and status codes.
* **Component behaviour:** `Livewire::test(...)` for actions, validation, redirects, events.
* **Deterministic fixtures:** Create complete records; don’t rely on null‑guards to pass tests.
* **Assertions:** Prefer model‑level assertions over raw DB queries when readable.
* **Forbidden actions:** Use try/fail/catch patterns rather than relying on vague status checks.

---

## 10) Migration Workflow (Repeatable Loop)

1. **Inventory:** List templates/components that contain Bulma classes or Vue directives (v‑if/v‑for/@click/etc.).
2. **Prioritise:** Batch by page or feature; migrate complete flows rather than scattered partials.
3. **Prepare:** Identify forms, tables, date pickers, and any custom widgets needing Flux equivalents.
4. **Convert Layout:** Ensure single root, swap to Flux layout, set width constraints and rhythm wrapper.
5. **Replace Components:** Map Bulma→Flux; convert forms to shorthand and date pickers to ISO; convert selects to Flux options.
6. **Port Behaviour:** Move logic to Livewire actions; choose Alpine only for micro‑interactions.
7. **Wire Routes & Policies:** Point routes at Livewire pages; enforce auth/policies in `mount()`.
8. **Delete Dead Code:** Remove Vue components/partials and Bulma styles; grep for leftovers.
9. **Test:** Run feature + Livewire tests; validate a11y basics (labels, keyboard, focus).
10. **Review & Commit:** Small, scoped commits with imperative messages. Include before/after screenshots for UI pages.

---

## 11) Anti‑Patterns to Avoid

* Leaving raw `<option>` tags under Flux selects.
* Relying on session flash success when the UX uses toasts/callouts.
* Recreating data blobs for the view instead of using relations.
* Multiple root elements in page views (causes Livewire errors).
* Sprinkling null‑guards to make failing tests pass.
* Mixing Vue remnants (directives, mount points) into migrated pages.

---

## 12) Edge‑Cases & Gotchas (Generalised)

* **Date formats:** Legacy UIs often used `d/m/Y`; ensure `Y-m-d` for values and correct parsing server‑side.
* **Numeric inputs:** Browsers can post empty strings; use validation to coerce and surface errors gracefully.
* **Disabled UI:** Some Flux controls don’t accept bare `disabled`; use boolean bindings (`:disabled="..."`).
* **Cursor affordance:** Tailwind reset removes default pointer on custom clickable containers—add `cursor-pointer` explicitly.
* **Pagination + filters:** Reset pagination when filters change to avoid empty pages.
* **Layout wrapping:** Livewire requires one root element per view; wrap stray nodes.

---

## 13) Code Review Checklist (Copy/Paste into PRs)

* [ ] No Vue directives/components; no Bulma classes remain.
* [ ] Layout uses a single root; width constraints and rhythm wrapper applied.
* [ ] Forms use Flux shorthand; date pickers use ISO; selects use `flux:select.option` only.
* [ ] Modern attribute bindings used (`:selected`, `:disabled`, `:required`).
* [ ] Interactions implemented via Livewire; Alpine only for micro‑UI.
* [ ] Eager‑loading prevents N+1 queries on lists.
* [ ] Success feedback uses toasts/callouts (not flash checks).
* [ ] A11y: labels present, focus visible, keyboard paths intact.
* [ ] Tests deterministic; fixtures complete; pagination resets on filter changes.
* [ ] Removed dead Vue/Bulma assets and references.

---

## 14) Final short guide

> **You are a Laravel migration assistant.** Convert legacy Bulma/Vue screens to Livewire v3 + FluxUI with Tailwind. Produce zero Vue and zero Bulma in the result. Use Flux components (with shorthand for fields), Tailwind for spacing/responsive/dark mode, `<flux:date-picker>` with ISO `Y-m-d`, and `<flux:select>` with only `<flux:select.option>` items using modern bindings. Move behaviour to Livewire actions; use Alpine only for micro‑interactions that don’t require server state. Prefer Eloquent relations and eager loading. Ensure a single root element, consistent layout widths, and vertical rhythm wrappers. Provide accessible labels and visible focus. Redirect using `$this->redirectRoute(...)`. Keep property types lenient at the boundary so validation handles bad input. Don’t add null‑guards to mask fixture issues; fix test data instead. Update routes to Livewire pages, delete dead Vue/Bulma code, and run the PR checklist before marking complete.

---

