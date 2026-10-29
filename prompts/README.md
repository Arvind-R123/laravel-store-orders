# Prompt Log

Screenshots of the actual prompts I gave the AI coding agent, in the order I used them. Long prompts are split into parts (a, b, c...).

| # | Files | Purpose |
|---|-------|---------|
| 01 | 01a-01h-master-prompt.png | Full spec prompt: role, schema, API, concurrency, job, tests, README |
| 02 | 02a-02b-followup-tests-concurrency-ui.png | Follow-up: full tests, concurrency command, Blade UI |
| 03 | 03-concurrency-demo-real.png | Agent left the demo as a stub; I demanded a real parallel-process implementation |
| 04 | 04-repo-state-recheck.png | Agent's earlier changes were missing on disk; I asked it to verify files exist |
| 05 | 05-retry-fixes-and-demo.png | Re-prompted for the real demo and missing tests |
| 06 | 06-fix-failing-tests.png | Fixed the 404/422 test and log channel error |
| 07 | 07a-07b-validation-job-tests.png | afterCommit job and validation edge-case tests |
| 08 | 08-ui-welcome-removal.png | Replace default welcome page with the billing UI |
| 09 | 09-ui-price-bug.png | I found a 100x price bug the agent missed |
| 10 | 10a-10c-ui-polish.png | Correctness plus UX polish |
| 11 | 11a-11c-ui-redesign.png | Professional redesign |
| 12 | 12-readme.png | README completion |
| 13 | 13-receipt-gst-fix.png | Receipt GST column and scrollbar fix |
| 14 | 14-receipt-nan-fix.png | NaN% bug fix |

## AI usage
AI wrote first drafts of the code. I reviewed the output, ran every command myself, and verified in a fresh clone (migrate --seed, 28 tests passing).
Issues I caught: stub concurrency demo (needed several re-prompts), changes missing from the working directory, 100x price bug in the UI, Boost config noise, NaN% in the receipt.
