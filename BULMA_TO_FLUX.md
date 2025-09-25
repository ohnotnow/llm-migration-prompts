# Playbook A — Bulma/Blade ➜ FluxUI + Tailwind (First Pass, No Livewire) — Revised

**Goal:** Replace Bulma markup and styles with FluxUI components + Tailwind utilities while preserving existing Blade/Vue/Livewire code **verbatim**. This is a mechanical UI pass. **Do not** introduce new Livewire or change/patch any JS behaviour apart from the case of the flux:date-picker (as mentioned - the format the date value expects has changed). Vue directives remain in the templates, and Vue is **not initialised** in this pass so broken interactivity reveals where behaviour exists.

---

## 1) Principles

* **Zero Bulma:** Remove all Bulma classes/components and dependencies. No mixing.
* **Strict preservation of code:** Keep all Blade, Livewire and Vue directives exactly as they were. Do **not** wrap, shim, or introduce Alpine or new Livewire.
* **Non‑disruptive to logic:** Do not change controllers, routes, requests, or any JS data flows.
* **Consistency:** Use Flux components and Tailwind spacing/typography.
* **A11y by design:** Use Flux labels/props to keep controls properly labelled.

---

## 2) Target State

* Blade templates render **FluxUI** components and **Tailwind** utilities.
* All prior Vue directives remain present in markup (but Vue is not initialised in this pass).
* **No Bulma** anywhere in the codebase.

---

## 3) Mapping Cheatsheet (Authoritative)

| Bulma                      | Flux/Tailwind replacement                  |         |       |            |
| -------------------------- | ------------------------------------------ | ------- | ----- | ---------- |
| `.columns/.column`         | `grid grid-cols-1 lg:grid-cols-2 gap-8`    |         |       |            |
| `.title/.subtitle`         | `<flux:heading>` / `<flux:text size="lg">` |         |       |            |
| `.box`                     | `<flux:card>`                              |         |       |            |
| `.message`/`.message-body` | `<flux:callout>`                           |         |       |            |
| `.button`                  | `<flux:button />`                          |         |       |            |
| `<hr>`                     | `<flux:separator />`                       |         |       |            |
| `.input`/`.textarea`       | `<flux:input>` / `<flux:textarea>`         |         |       |            |
| `.select > select`         | `<flux:select>` + `<flux:select.option>`   |         |       |            |
| Responsive helpers         | Tailwind (`hidden sm:block`, etc.)         |         |       |            |

---

## 4) Canonical Form Patterns

* **Shorthand fields:** `<flux:input name="title" label="Title" />`
* **Selects (no raw option tags):**

```blade
<flux:select name="role" label="Role">
  <flux:select.option value="admin" :selected="$role==='admin'">Admin</flux:select.option>
  <flux:select.option value="user"  :selected="$role==='user'">User</flux:select.option>
</flux:select>
```

* **Date fields (always convert):** Replace **all date inputs**—both `type="date"` and any inputs using Pikaday—with Flux date picker; **values in ISO `Y-m-d`**.

```blade
<flux:date-picker name="date" value="{{ old('date', now()->format('Y-m-d')) }}" label="Date" />
```

* **Email inputs:** Use `type="email"`.
* **File inputs:** `<flux:input type="file" name="file" accept="..." label="..." />`
* **Bindings:** Use modern Blade bindings: `:selected`, `:disabled`, `:required`.

---

## 5) Layout & Spacing

* Ensure each main (not partial) template uses `<x-layouts.app>`; ensure a **single root** node in views.
* Wrap form content in `div.flex-1.space-y-6` for vertical rhythm.
* Use width constraints: `max-w-xl`, `max-w-2xl`, `max-w-4xl`.
* Don’t add margins to `<flux:separator />` when inside rhythm wrappers.
* Add `cursor-pointer` to clickable containers (Tailwind reset removes default pointer on non‑button elements).

---

## 6) Vue Coexistence (Strict Preservation in Pass A)

* **Preserve all Vue directives and code verbatim** (`v-if`, `v-for`, `:prop`, `@click`, etc.).
* **Do not initialise Vue** in this pass. Broken interactions are expected and help identify behaviour to migrate later.
* **No shims/wrappers:** Do **not** add Alpine or extra wrappers to make directives work on Flux tags. This pass is a straight Bulma ➜ Flux swap.

---

## 7) Safety & A11y

* Ensure every interactive control has an accessible label (use Flux `label` props or `<flux:label>`).
* Preserve semantic structure (headings, lists, buttons vs links) when swapping components.
* **Flux handles focus outlines and dark mode styling**—no extra outline or dark‑mode classes required.

---

## 8) Workflow (Repeatable)

1. **Inventory:** Use the pre‑generated `TEMPLATES_TODO.md` from the finder script.
2. **Convert layout:** Ensure a single root node; keep the Blade layout; apply width constraints (`max-w-*`).
3. **Replace components:** Map Bulma → Flux per cheatsheet; use field shorthand and modern Blade bindings.
4. **Dates:** Convert **all date fields** (both `type="date"` and Pikaday) to `<flux:date-picker>` with ISO `Y-m-d`.
5. **Responsive:** Replace Bulma responsive helpers with Tailwind utilities.
6. **Vue left as‑is, uninitialised:** Expect broken interactivity; that’s the point.
7. **Commit:** Small, focused diffs per template.

---

## 9) Anti‑Patterns (Blockers)

* **Any Bulma left anywhere** (classes, components, or dependencies).
* Raw `<option>` elements nested inside `<flux:select>`.
* Patching or modifying existing JS/Vue (no Alpine, no wrappers, no initialising Vue).
* Multiple root elements in Blade views.

---

## 10) Checklist (Per Template)

* [ ] No Bulma classes/components remain.
* [ ] Flux components used with shorthand where possible.
* [ ] Selects use `<flux:select.option>` only; modern bindings applied.
* [ ] **All date fields** converted to `<flux:date-picker>` with ISO `Y-m-d`.
* [ ] Layout has width constraint and `space-y-6` rhythm.
* [ ] Responsive classes are Tailwind equivalents.
* [ ] A11y labels preserved; semantics intact.
* [ ] Vue directives preserved; Vue not initialised.

---

## 11) Ready‑to‑Use System Prompt (Pass A)

** You are performing a first‑pass UI migration. ** Replace all Bulma markup/classes with FluxUI + Tailwind in Blade templates. Preserve all existing Vue directives and code verbatim and **do not initialise Vue** in this pass. Do not add wrappers or Alpine shims to make things work; broken interactions are expected and help discovery later. Use Flux component shorthand for fields; convert **every date field** (including `type="date"` and Pikaday inputs) to `<flux:date-picker>` with ISO `Y-m-d`. Use `<flux:select>` with `<flux:select.option>` only and modern Blade bindings (`:selected`, `:disabled`, `:required`). Ensure a single root element, consistent width constraints, spacing wrappers, and responsive Tailwind classes, with accessible labels. Do not modify controllers, routes, or any JS behaviour. Commit small, focused diffs and run the checklist before marking complete.

