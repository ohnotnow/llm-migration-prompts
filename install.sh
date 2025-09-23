#!/usr/bin/env bash

set -e

if [[ -z "$1" ]]; then
  echo "Usage: $0 <project-dir>"
  exit 1
fi

PROJECT_DIR="$1"

mkdir -p "$PROJECT_DIR/bin"
mkdir -p "$PROJECT_DIR/.claude/commands"
cp -r bin/* "$PROJECT_DIR/bin"
cp claude_bulma_flux.md "$PROJECT_DIR/.claude/commands/migrate-bulma.md"
cp claude_vue_livewire.md "$PROJECT_DIR/.claude/commands/migrate-vue.md"
cp FLUX_PATTERNS.md "$PROJECT_DIR"
cp BULMA_TO_FLUX.md "$PROJECT_DIR"
cp FINAL_TO_LIVEWIRE.md "$PROJECT_DIR"

echo 'Done!'
