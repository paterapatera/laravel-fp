---
name: sdd-steering
description: Create or resynchronize steering documents from the code. Use when the user runs /sdd-steering.
---

# sdd-steering

Docs directory: `docs/sdd`. Write bodies in `ja`. Base branch: `main`.

Run `bun .agents/skills/sdd-scripts/sdd.mjs check --scope setup` first. Continue when the only failure is `SETUP_STEERING_MISSING`. Stop on every other setup failure. Do not create an issue directory and do not create a branch. Stop when `docs/sdd/issues/<current-branch>/` exists.

If `steering/` is missing, create it. If it exists, resynchronize it. When a required file is missing during resync, create that file. There is no mode flag.

## Required files and headings

`example.md` next to this file shows all five required files in a form that passes `check`. Copy its frontmatter, heading order, glossary term level, and link form, and read `.agents/skills/sdd-scripts/sdd.mjs` only when `check` reports something this skill and the example do not explain.

Keep this order:

- `product.md`: Purpose, Stakeholders and Users, Capabilities Overview, External Systems, Operational Scenarios, Constraints, Related
- `glossary.md`: Terms, Related
- `architecture.md`: Overview, Quality Priorities, Context View, Component View, Runtime View, Deployment View, Cross-cutting Concerns, Related
- `tech.md`: Stack, Development Environment, Common Commands, Coding Conventions, Testing Approach, Related
- `structure.md`: Repository Layout, Module Boundaries, Naming Conventions, Dependency Rules, Code-to-Specs Mapping, Related
- `<topic>.md`: Purpose, Rules, Related

Frontmatter is `type: Steering`, `title`, `description`, and lowercase kebab-case `tags`. Write in the present tense. Do not copy requirements, feature names, or secrets. Keep each file near 300 lines. Capabilities Overview links to domains, not capabilities. Put cross-cutting quality targets in `specs/`, not in steering. Sort glossary terms by Unicode code point, case-insensitive for letters.

## Resync

Compare steering with the code: manifests and commands for `tech.md`, directories and imports for `structure.md`, module dependencies and deploy config for `architecture.md`, and matching code for topic files. Report `product.md` and `glossary.md` differences without editing them. Present each difference as current text, code fact, and proposed text. The user accepts, rejects, or defers one item at a time. Apply only accepted items.

If code contradicts an Accepted ADR, report `ADR-<n>` and do not edit steering. Report an ADR candidate without creating an ADR. Append accepted edits to `steering/log.md` as `* **Resync**: ...` with no issue name. Do not record rejections.

After either mode, run `bun .agents/skills/sdd-scripts/sdd.mjs index` and `bun .agents/skills/sdd-scripts/sdd.mjs check`.

## Other skills

When another skill notices an unrelated steering drift, it finishes its work and then recommends `/sdd-steering`.
