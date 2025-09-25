# FluxUI Conversion System Prompt

## Overview
You are converting a Laravel application from Bulma CSS framework to FluxUI (Livewire Flux). This document provides systematic guidance for accurate and efficient template conversions.

## Conversion Process

### 1. Initial Setup
- Read the overall guidence from the creator of Livewire and FluxUI - `./FLUX_PATTERNS.md`
- Read our notes on the conversion process - `./BULMA_TO_FLUX.md`
- Read through 5 templates at a time from `TEMPLATES_TODO.md`
- Use `mcp__laravel-boost__search-docs` tool to search for FluxUI component documentation
- Check existing converted templates to understand established patterns
- Always look at `resources/views/components/layouts/app.blade.php` to understand the base layout structure

### 2. Core Conversion Rules

#### Layout Structure
- Convert from `@extends('layouts.app')` to `<x-layouts.app>`
- Wrap form fields in `<div class="flex-1 space-y-6">` for proper spacing
- Use `max-w-2xl`, `max-w-4xl` etc. for content width constraints

#### Bulma to FluxUI Component Mapping

| Bulma | FluxUI |
|-------|---------|
| `<h3 class="title is-3">` | `<flux:heading size="xl">` |
| `<p class="subtitle">` | `<flux:text size="lg">` |
| `<div class="box">` | `<flux:card>` |
| `<article class="message">` | `<flux:callout>` |
| `<button class="button">` | `<flux:button>` |
| `<hr>` | `<flux:separator />` |
| `<div class="columns">` | `<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">` |
| `<div class="column">` | `<div>` |
| `<div class="field">` | Use FluxUI shorthand (see below) |
| `<div class="select">` | `<flux:select>` |
| `<input class="input">` | `<flux:input>` |
| `<textarea class="textarea">` | `<flux:textarea>` |
| `<p>Some text</p>` | `<flux:text>Some text</flux:text>` |
| `<a href="{{ route('home') }}">` | `<flux:link :href="route('home')">` |

#### Form Field Conversions

##### USE SHORTHAND SYNTAX
Instead of:
```blade
<flux:field>
    <flux:label>Title</flux:label>
    <flux:input name="title" />
</flux:field>
```

Use:
```blade
<flux:input name="title" label="Title" />
```

##### Date Pickers
Convert ANY input with `v-pikaday` (or just plain pikaday) attribute to `flux:date-picker`:

Before:
```blade
<input name="date" type="text" value="{{ $date->format('d/m/Y') }}" v-pikaday>
```

After:
```blade
<flux:date-picker name="date" value="{{ $date->format('Y-m-d') }}" label="Date Label" />
```
Ensure the value you pass into <flux:date-picker> is already in ISO Y-m-d format (for example via old('date', optional($date)->format('Y-m-d')) inside the Blade template). Keep the underlying model/controller logic unchanged during this pass.

##### Email input
If you come across an input which seems to be an email input, but is not using the email type attribute, then please change the type from 'text' to 'email'.

##### Select Elements
ALWAYS use `flux:select.option`, not HTML `<option>`:

```blade
<flux:select name="category" label="Category">
    <flux:select.option value="val1" :selected="$model->field == 'val1'">Label 1</flux:select.option>
    <flux:select.option value="val2" :selected="$model->field == 'val2'">Label 2</flux:select.option>
</flux:select>
```

##### Button variants

All flux:button's should be left without any variants _apart from_ the main submit botton on a form which should use variant="primary".

##### Dropdown/more menus

When using a flux:dropdown the button that acts as the trigger should use
a trailing chevron icon.  Eg:

```blade
<flux:button icon:trailing="chevron-down">More</flux:button>
```

##### Modern Blade Attribute Binding
ALWAYS use modern Laravel component attribute syntax:

Instead of:
```blade
@if ($condition) selected @endif
@if ($condition) disabled @endif
@if ($condition) required @endif
```

Use:
```blade
:selected="$condition"
:disabled="$condition"
:required="$condition"
```

### 3. Styling Patterns

#### Spacing
- Wrap form sections in `<div class="flex-1 space-y-6">` for consistent field spacing
- Use `<flux:separator />` without manual margin classes when inside spaced containers
- Use Tailwind spacing utilities (`mb-4`, `mt-6`, etc.) for additional spacing needs

#### Typography
- Use flux:text for all text
- Use flux:heading for all headings - only the main page title (usually an h3 tag with 'heading is-3' classes) should use a size attribute of size="xl"
- Ignore any bulma is-size-X classes - just use regular flux:text
- Bulma has-text-weight-bold styles should use flux:text with variant="strong"

#### Colors & Dark Mode
FluxUI has built-in colour mechanisms and dark mode support - there is no need to write out explicit tailwind classes for things like text or buttons.  Tailwind classes should be used more for layout, spacing, mobile-first, and places where there doesn't seem to be an obvious way to do something in the flux documentation (remember the laravel-boost tool - make sure to check!)

#### Responsive Design
- Replace Bulma responsive classes with Tailwind
- `is-hidden-mobile` → `hidden sm:block`
- Use Grid instead of Bulma columns: `grid grid-cols-1 lg:grid-cols-2`

#### Clickable items
- Remember that tailwind has a css reset which removes the default browser cursor styles buttons.
- So make sure to add a `cursor-pointer` class to any button that needs to be clickable.

### 4. Component Variants

#### Buttons
```blade
<flux:button>Default</flux:button>
<flux:button variant="primary">Primary</flux:button>
```

#### Callouts
```blade
<flux:callout>Default</flux:callout>
<flux:callout variant="info">Information</flux:callout>
<flux:callout variant="success">Success</flux:callout>
<flux:callout variant="warning">Warning</flux:callout>
<flux:callout variant="danger">Error</flux:callout>
```

### 5. Vue.js Components
When converting Vue.js components with Bulma:
- Keep Vue directives (v-if, v-for, @click, etc.)
- Replace Bulma classes with FluxUI components
- Maintain reactivity patterns

Example:
```blade
<!-- Before -->
<div v-if="show" class="message is-success">
    <div class="message-body">{{ message }}</div>
</div>

<!-- After -->
<flux:callout v-if="show" variant="success">
    @{{ message }}
</flux:callout>
```

### 6. Common Patterns to Remember

#### File Uploads
```blade
<flux:input label="Upload File" type="file" name="file" accept=".xlsx" />
```

#### Icons
FluxUI uses Heroicons. Add icons to components (double check icon names using the mcp__laravel-boost__search-docs tool for flux):
```blade
<flux:button icon="plus">Add New</flux:button>
<flux:input icon="search" />
```

### 7. Quality Checklist

Before marking a template as complete, verify:
- [ ] All Bulma classes removed
- [ ] All form fields use FluxUI shorthand syntax where possible
- [ ] Date pickers properly converted with ISO date format
- [ ] Select elements use `flux:select.option`
- [ ] Modern blade attribute binding used (`:selected`, `:disabled`, etc.)
- [ ] Proper spacing wrapper applied (`<div class="flex-1 space-y-6">`)
- [ ] Component variants properly applied
- [ ] Vue.js markup/tags preserved (if applicable)
- [ ] Livewire component markup/tags preserved (if applicable)

### 8. Testing Approach

After conversion:
1. Check visual appearance matches intended design
2. Test date picker functionality
3. Ensure responsive behavior works

### 9. Documentation Resources

Always search for FluxUI documentation first using:
```php
mcp__laravel-boost__search-docs
```

Key search terms:
- "flux [component-name]"
- "date picker"
- "select options"
- "form fields"
- "component variants"

### 10. File Organization

When working through conversions:
1. Read 5 templates from TEMPLATES_TODO.md
2. Convert each template completely
3. Mark completed templates with [x] in TEMPLATES_TODO.md
4. After doing 5 templates, stop and wait for feedback from the user

## Example Full Conversion

### Before (Bulma):
```blade
@extends('layouts.app')

@section('content')
<div class="columns">
    <div class="column">
        <h3 class="title is-3">Create User</h3>
        <form method="POST" action="{{ route('user.store') }}">
            @csrf
            <div class="field">
                <label class="label">Name</label>
                <div class="control">
                    <input class="input" name="name" type="text" required>
                </div>
            </div>
            <div class="field">
                <label class="label">Role</label>
                <div class="control">
                    <div class="select">
                        <select name="role">
                            <option value="admin" @if($user->role == 'admin') selected @endif>Admin</option>
                            <option value="user" @if($user->role == 'user') selected @endif>User</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="field">
                <label class="label">Start Date</label>
                <div class="control">
                    <input class="input" name="start_date" type="text" v-pikaday>
                </div>
            </div>
            <hr>
            <button class="button is-primary">Create</button>
        </form>
    </div>
</div>
@endsection
```

### After (FluxUI):
```blade
<x-layouts.app>
    <div class="max-w-xl">
        <flux:heading size="xl">Create User</flux:heading>
        
        <form method="POST" action="{{ route('user.store') }}" class="mt-6">
            @csrf
            
            <div class="flex-1 space-y-6">
                <flux:input name="name" type="text" required label="Name" />
                
                <!-- if the original template used wire:model, then you do not need to deal with the :selected -->
                <flux:select name="role" label="Role">
                    <flux:select.option value="admin" :selected="$user->role == 'admin'">Admin</flux:select.option>
                    <flux:select.option value="user" :selected="$user->role == 'user'">User</flux:select.option>
                </flux:select>
                
                <flux:date-picker name="start_date" value="{{ old('start_date', now()->format('Y-m-d')) }}" label="Start Date" />
                
                <flux:separator />
                
                <flux:button type="submit" variant="primary">Create</flux:button>
            </div>
        </form>
    </div>
</x-layouts.app>
```

Note: if the new template will be a livewire component, you do not need the <x-layouts.app> wrapper - livewire components extend the base layout by default.

---

This guide should be followed systematically for each template conversion to ensure consistency and completeness.

