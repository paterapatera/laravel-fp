---
name: sdd-design
description: Research options and write the design delta for the current SDD issue. Use when the user runs /sdd-design.
---

# sdd-design

Docs directory: `docs/sdd`. Write bodies in `ja`. Issue directory: `docs/sdd/issues/<current-branch>/`. Stop when it is missing.

Run `bun .agents/skills/sdd-scripts/sdd.mjs check --scope setup` first. Stop when `requirements.md` has an open `- [ ] Q` item, and tell the user to finish `/sdd-req`.

`example-research.md` and `example-design.md` next to this file pass `check` together with the `/sdd-req` example. Copy their headings, field names, and table shapes, and read `.agents/skills/sdd-scripts/sdd.mjs` only when `check` reports something this skill and the examples do not explain. Write the bodies in `ja`, and keep only the parts the issue needs.

## research.md

Write Questions (`RQn`), Findings, Options (`Dn`), Conclusions, and `# Delta`. Every finding bullet ends with `(source: ...)`: `path:line`, an id, a URL with an access date, or a command plus its result. ADR candidates are decisions that are expensive to reverse, that affect more than one capability, or that change specs or steering. Ask the user to choose those, at most 5 questions at a time, and recommend one option. Ask the next 5 after the answers arrive; rounds have no limit. Each question decides one thing, and each option states only the result that differs. Put background in one or two chat sentences before the questions, not in them. Keep a question near 30 characters and an option near 15 in ja, or near 12 and 6 words otherwise, not counting its score. Prefix each option with a score from 1 to 5, as in `[5] ...`, where 5 is best. The score weighs safety most, then closeness to the ideal form, then low implementation effort. List options from the highest score. Decide the other points yourself. Each conclusion is `Dn: <choice> -> ADR-NEW-<k>` or `-> No ADR (<reason>)`.

An added ADR is readable without `research.md`. Use MADR headings two levels deeper than the permanent file. Do not write Status or Date. `SUPERSEDED`, `DEPRECATED`, and `AMENDED` follow the delta vocabulary: `### ADR-<n> -> ADR-NEW-<k>` under `## SUPERSEDED`, `### ADR-<n>` with `- reason:` under `## DEPRECATED`, and `### ADR-<n>` with `- title:`, `- description:`, `- tags:`, or `#### Related` under `## AMENDED`. `AMENDED` is only a typo, link, title, description, tags, or Related. Supersede a decision instead of rewriting it.

## design.md

Write Overview, Requirements Traceability, Components and Interfaces, Data Model, Error Handling, Testing Strategy, Migration, and `# Delta`. Trace every ADDED, MODIFIED, and REMOVED id to components and a test level: Unit, Integration, Contract, E2E, Performance, Security, or Manual. Use `none` for traceability when specs do not change, and then say that Testing Strategy runs the full existing test suite. Data Model, Error Handling, and Migration are `none` when unused.

Stop and return to `/sdd-req` when the design needs an interface or data change that is not in the requirements delta. Steering deltas here are only `architecture.md`, `tech.md`, `structure.md`, and topic files. Follow steering. When the design departs from it, change steering in the delta and add `rationale` when the ADR rule applies. Put `- rationale: ADR-NEW-<k>` or `- rationale: ADR-<n>` on the first line under the changed heading.

`Runs on` is Local, CI, Staging, or Human. Contract tests check the shape without the counterpart running. Performance tests measure latency, throughput, or error rate. Security tests include SAST, DAST, or vulnerability checks.

## Finish

Run stamp and check. Commit `research.md` and `design.md` together as one `docs` commit. Stage only this issue directory.
