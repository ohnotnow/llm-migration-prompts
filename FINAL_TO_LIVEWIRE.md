# Playbook B — Blade/Vue ➜ Livewire v3 + FluxUI (Second Pass, Remove Vue) — Revised

**Goal:** Migrate behaviour and state from Blade/Vue to **Livewire v3** using **FluxUI** components. Remove Vue entirely. This is a behavioural pass: server‑driven state, events, and validation.

---

## 1) Principles

* **Zero Vue:** Remove all Vue components, directives, and mount points.
* **Server truth:** Livewire holds state; Eloquent relationships power queries; no duplicated JS state.
* **Minimal client JS:** Use Alpine only for micro‑interactions that don’t need server state.
* **Auth & policies:** Enforce in `mount()`/actions; keep sensitive logic server‑side.
* **Deterministic tests:** Prefer Livewire/component tests for actions and GET route tests for visibility.

---

## 2) Target Architecture

* **Full‑page Livewire components** for screens with meaningful interaction; child components only for proven reuse.
* **Routes** point directly to Livewire classes; use `$this->redirectRoute(...)` for navigation.
* **Layouts** via Blade components; ensure a single root element for the view. **When converting a Blade template into a Livewire page view, do *not* wrap with `<x-layout ...>` — Livewire applies the page layout automatically.**
* Use Flux components for UI widgets and Tailwind utilities (including dark mode).

---

## 3) State, Actions, Validation

* Define public properties for bound fields; prefer relaxed boundary typing (`int|string`) so validation handles coercion.
* Use `rules()`/`validate()` or attributes for validation. Surface errors via Flux inputs/callouts.
* Common actions: `save()`, `destroy()`, `filter/update…()`; emit toasts using your Flux toast helper.
* Reset pagination when filters change.

---

## 4) Replacing Vue Patterns

| Vue pattern                | Livewire replacement                                        |
| -------------------------- | ----------------------------------------------------------- |
| `v-if`/`v-show`            | Blade conditionals or computed properties in the view model |
| `v-for`                    | Blade `@foreach`                                            |
| `v-model`                  | `wire:model` / `wire:model.live` as needed                  |
| `@click`/`@submit.prevent` | `wire:click="method"` / `wire:submit.prevent="method"`      |
| Axios/fetch calls          | Livewire actions with redirects/returns                     |
| Client‑side filters/sort   | Server‑driven queries + pagination                          |
| Event bus/toasts           | Server events + Flux toasts                                 |

---

## 5) Tables & Filters (Server‑Driven)

* Store filters in `public array $filters` with typed keys.
* Query with relationships (`with`, `whereHas`) and paginate.
* Use `wire:key` on rows for stable DOM diffs.
* Provide explicit “All …” options to clear filters.

---

## 6) Navigation & Side Effects

* Use `$this->redirectRoute('name', [...])` in actions; no `RedirectResponse` type hints.
* Dispatch domain events and notifications (mail/jobs) from actions immediately after persistence.
* For destructive actions, confirm via Flux modals; expose a boolean to the view to drive `:disabled`/visibility.

---

## 7) Accessibility & UX

* Preserve keyboard flows and focus management (e.g., focus first invalid field on validation failure where possible).
* Prefer toasts/callouts for feedback over session flash.
* Keep segmented, radio, and select controls keyboard‑navigable.

---

## 8) Data & Performance

* Eager‑load relations for list screens.
* Transform paginator collections when shaping payloads, preserving metadata.
* Avoid heavy client reactivity; let Livewire diffs handle UI updates.

---

## 9) Deleting Vue Safely

1. Remove Blade mount points and component tags.
2. Remove script imports/registrations from your build.
3. Grep for dead references (components, helpers, global config).
4. Verify no routes/controllers still feed JSON payloads for the old Vue components.

---

## 10) Workflow (Repeatable)

1. **Select scope:** Choose a fully Flux‑ified screen from Pass A.
2. **Create Livewire component:** Full‑page by default; add layout metadata if needed (but do **not** wrap the view with `<x-layout ...>`).
3. **Hydrate state:** In `mount()`, eager‑load resources; enforce policies.
4. **Wire UI:** Replace Vue directives with `wire:` bindings; use Flux inputs/selects/radios.
5. **Implement actions:** Save/delete/filter with validation and events; add toasts.
6. **Route:** Map route directly to the component; update nav links.
7. **Remove Vue:** Delete mounts/imports; eliminate leftover JSON view payloads.
8. **Test:** Livewire tests for actions/validation/redirects; feature GET for visibility.
9. **Commit:** Small diffs; include screenshots/GIFs for interactive flows.

---

## 11) Common Gotchas

* **Multiple root elements** in the view → wrap in a single container.
* **Disabled bindings:** some Flux components require boolean bindings (e.g., `:disabled="…"`) rather than bare attributes.
* **Pagination after filter change:** reset page to `1` to avoid empty results.
* **Numeric inputs posting ''** → use relaxed types at the boundary; rely on validation.
* **Cursor affordance** on custom clickable containers (add `cursor-pointer`).

---

## 12) Checklists

### Per Screen

* [ ] Vue removed (no directives, no mounts/imports).
* [ ] Routes point to Livewire page; nav updated.
* [ ] **No `<x-layout ...>` wrapper** in the Livewire page view; layout applied by Livewire.
* [ ] Flux components with proper bindings; accessible labels.
* [ ] Livewire actions implemented with validation, events, and toasts.
* [ ] Queries eager‑load relations; pagination + filters reset.
* [ ] Tests updated: Livewire behaviour + GET visibility.

### Exit Criteria for Pass B

* [ ] No Vue or legacy JS payloads remain.
* [ ] Server‑driven state and interactions work end‑to‑end.
* [ ] Deterministic tests pass.

---

## 13) Ready‑to‑Use System Prompt (Pass B)

> **You are performing a second‑pass behavioural migration.** Replace Blade/Vue screens with Livewire v3 + FluxUI. Remove all Vue and client‑side data flows. Livewire holds state; use Eloquent relationships and eager loading. Bind with `wire:model`/`wire:click`, validate on the server, and provide feedback via Flux toasts. Use `$this->redirectRoute(...)` for navigation. Maintain accessibility (labels, focus, keyboard). Reset pagination on filter changes. Keep property types lenient at the boundary so validation guards bad input. Delete Vue mounts/imports and any legacy JSON payloads feeding them. **Do not wrap Livewire page views in `<x-layout ...>` — Livewire applies the layout.** Commit small diffs and verify with Livewire and feature tests before marking complete.

---


