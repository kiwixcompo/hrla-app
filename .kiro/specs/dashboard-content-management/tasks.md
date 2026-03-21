# Implementation Tasks

## Task 1: Seed dashboard content defaults in includes/content.php
- [x] Add 16 dashboard keys to `insertDefaultContent()` in `includes/content.php`

## Task 2: Add dashboard keys to data/content.json fallback
- [x] Add all 16 dashboard keys with default values to `data/content.json`

## Task 3: Add Dashboard tab to admin/index.php
- [x] Add tab button in the `.content-tabs` bar
- [x] Add tab content form with Welcome, Federal, and California sections
- [x] Pre-populate form fields with `getContent()` calls

## Task 4: Update dashboard.php to use getContent()
- [x] Replace all hardcoded strings with `getContent()` calls
- [x] Restructure disclaimer bullets as `<ul><li>` elements
