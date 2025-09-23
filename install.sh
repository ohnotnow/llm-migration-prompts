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
cp claude_command.md "$PROJECT_DIR/.claude/commands/migrate-bulma.md"
cp FLUX_PATTERNS.md "$PROJECT_DIR"
cp MIGRATION_PLAYBOOK.md "$PROJECT_DIR"

echo 'Done!'
