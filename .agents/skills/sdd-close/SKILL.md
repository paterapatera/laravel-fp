---
name: sdd-close
description: Apply the issue delta to permanent docs and prepare the pull request text. Use when the user runs /sdd-close.
---

# sdd-close

Docs directory: `docs/sdd`. Bodies stay in `ja`. Base branch: `main`. Issue directory: `docs/sdd/issues/<current-branch>/`.

Run `bun .agents/skills/sdd-scripts/sdd.mjs check --scope setup` first. Only this skill updates permanent specs, decisions, and steering for an issue. Implementation reads the current docs plus the issue delta. Open `.agents/skills/sdd-scripts/sdd.mjs` only for a report this skill does not explain.

## Normal close

1. Merge `main`. Stop and ask the user to resolve conflicts.
2. Run `bun .agents/skills/sdd-scripts/sdd.mjs apply --issue <feature>`. The script allocates ids, rewrites links, regenerates indexes, appends logs without issue names, and replaces provisional ids in Git-tracked code outside the docs directory. It writes nothing when check fails.
3. Run `bun .agents/skills/sdd-scripts/sdd.mjs check --stage close --issue <feature>`. Stop and report the cause on failure. A base mismatch means `/sdd-fix` at L2: delete the stale `base:` line, stamp, and retry.
4. Read the proposal, requirements, and design for the pull request text. Then delete the issue directory and commit as `docs: <description>` with the description in `ja`; a tracker key prefix becomes the scope. Stage permanent docs, code whose provisional ids changed, and the issue deletion. Do not use `git add -A`.
5. Print only the pull request title and body. Title is `【<key>】<proposal title>` when the feature name has a tracker key, otherwise the proposal title. Build the body from the proposal's Why, What Changes, and Out of Scope, rewritten to what the requirements and design settled; drop choices the proposal left open and list the added ADRs by title. Add `Post-merge verification` checkboxes for CI and Staging rows from Testing Strategy. Do not print `Closes #n`, and do not push or open the pull request.

Stop when any major task is unchecked. A docs-only issue skips task completion and Post-merge verification, and stops while Open Questions remain. After close, further changes are a new issue.

## Re-close

Use this mode when the issue directory is gone and this branch already has a close commit.

1. Merge `main`.
2. Resolve permanent-doc conflicts by keeping both requirements and ADRs, ordering `log.md` by date, and keeping either `index.md`. Stop for code conflicts.
3. Run `bun .agents/skills/sdd-scripts/sdd.mjs renumber --base main`. It renumbers only ids this branch added that collide with the base, and it does not change ids already on the base. When it stops for a capability code collision, propose a new code, get confirmation, and rerun with `--code OLD=NEW`.
4. Run index and `check --stage close`, then commit as `docs: <description>` in `ja`.

This package recommends that pull request CI run `check` as a required status check, and that the base branch be up to date before merge. Those are not requirements, and they are not part of the close output.
