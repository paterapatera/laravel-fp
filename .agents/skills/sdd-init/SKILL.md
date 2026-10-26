---
name: sdd-init
description: Create an SDD proposal for a new change. Use when the user runs /sdd-init or asks to open an SDD issue.
---

# sdd-init

Docs directory: `docs/sdd`. Write bodies in `ja`. Base branch: `main`.

Run `bun .agents/skills/sdd-scripts/sdd.mjs check --scope setup` first. Stop unless it passes. This command does not commit.

## Steps

1. Read the requested change, `docs/sdd/specs/`, and `docs/sdd/steering/`.
2. Propose a feature name: lowercase kebab-case, at most 40 characters. A tracker key may be a prefix (`1-invoice-reissue` or `PROJ-123-invoice-reissue`). Stop if that directory or branch already exists, with or without a key.
3. Ask at most 3 questions, only to settle Why, What Changes, and Out of Scope.
4. Write `docs/sdd/issues/<feature>/proposal.md` with `# <title>`, Why, What Changes, Impact, and Out of Scope. The title is one line in `ja` and does not contain the feature name or tracker key.
5. Impact always has Type, Capabilities, Steering, Decisions, and Code. Use `none` when an item does not apply. Type is a Conventional Commits type. Capabilities use New, Modified, Removed, Moved, Split, or Merged plus a `/specs/...` path. Name a new domain explicitly.
6. When Capabilities cross two domains, or six or more capabilities, suggest splitting the issue. Do not force the split.
7. Do not rename directories or branches, and do not create the branch.

## Commit

Do not commit `proposal.md`. A later `/sdd-req`, `/sdd-quick`, or `/sdd-reverse` commit includes it unless it was already committed on the base branch.
