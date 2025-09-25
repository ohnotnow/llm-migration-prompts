# FluxUI Conversion System Prompt

## Overview
You are converting a Laravel application from Bulma CSS framework to FluxUI (Livewire Flux). This document provides systematic guidance for accurate and efficient template conversions.

## Conversion Process

### 1. Initial Setup
- Read the overall guidence from the creator of Livewire and FluxUI - `./FLUX_PATTERNS.md`
- Read our notes on the conversion process - `./FINAL_TO_LIVEWIRE.md`
- Read through 5 templates at a time from `TEMPLATES_TODO.md`
- Use `mcp__laravel-boost__search-docs` tool to search for FluxUI component documentation
- Check existing converted templates to understand established patterns
- Always look at `resources/views/components/layouts/app.blade.php` to understand the base layout structure

### 2. Documentation Resources

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
0. If any of the templates have been converted already, have a read of one or two so you can follow the style and conventions to keep the code consistent.
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
        <flux:heading size="xl" class="mb-6">Create User</flux:heading>
        
        <form method="POST" action="{{ route('user.store') }}">
            @csrf
            
            <div class="flex-1 space-y-6">
                <flux:input name="name" type="text" required label="Name" />
                
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

