# Design Document

## Overview

This design extends the existing Content Management system in `admin/index.php` to add a "Dashboard" tab. The tab lets admins edit all hardcoded text in `dashboard.php` — welcome heading, subheading, tool button labels, and the full disclaimer/limitation blocks for both the Federal and California tools. Changes are persisted via the existing `save_content` action in `api/admin.php` to the `site_content` MySQL table, with `data/content.json` as fallback. `dashboard.php` is updated to read from the Content_Store instead of using hardcoded strings.

No new API endpoints or files are needed. Everything slots into the existing patterns.

---

## Architecture

### Affected Files

| File | Change |
|---|---|
| `admin/index.php` | Add "Dashboard" tab button + tab content form |
| `dashboard.php` | Replace hardcoded strings with `getContent()` calls |
| `includes/content.php` | Add dashboard default content to `insertDefaultContent()` |
| `data/content.json` | Add dashboard keys as file-based fallback defaults |

### No Changes Needed

- `api/admin.php` — existing `save_content` action handles any category generically
- `assets/css/admin.css` — existing `.content-tab`, `.form-group`, `.step-group` styles apply

---

## Data Model

### New Content Keys (category: `dashboard`)

All keys are inserted via `insertDefaultContent()` in `includes/content.php` with `INSERT IGNORE` so existing values are never overwritten.

#### Welcome Section

| content_key | content_type | Default Value |
|---|---|---|
| `dashboard_welcome_heading` | `text` | `Welcome back, {name}` |
| `dashboard_welcome_subheading` | `textarea` | `Choose a compliance tool to generate professional leave responses` |

#### Federal Tool

| content_key | content_type | Default Value |
|---|---|---|
| `dashboard_federal_tool_label` | `text` | `Federal Leave Assistant` |
| `dashboard_federal_disclaimer_heading` | `text` | `Federal-Specific Limitations` |
| `dashboard_federal_disclaimer_intro` | `textarea` | `Focuses employment laws, including but not limited to the Family and Medical Leave Act (FMLA) and the Americans with Disabilities Act (ADA), and to state regulations are not covered within this version. Responses are limited to federal law.` |
| `dashboard_federal_disclaimer_bullet_1` | `textarea` | `Does not account for state or local leave laws that may provide additional or different protections` |
| `dashboard_federal_disclaimer_bullet_2` | `textarea` | `Does not evaluate collective bargaining agreements, institutional policies, or employer-specific practices` |
| `dashboard_federal_disclaimer_bullet_3` | `textarea` | `Does not implement and act upon legal advice such as the ADA interactive process or individualized eligibility determinations` |
| `dashboard_federal_disclaimer_footer` | `textarea` | `Users are responsible for confirming current federal requirements and seeking legal advice when appropriate.` |

#### California Tool

| content_key | content_type | Default Value |
|---|---|---|
| `dashboard_california_tool_label` | `text` | `California Leave Assistant` |
| `dashboard_california_disclaimer_heading` | `text` | `California-Specific Limitations` |
| `dashboard_california_disclaimer_intro` | `textarea` | `California employment laws, including but not limited to the California Family Rights Act (CFRA), Pregnancy Disability Leave (PDL), and related state-specific employment and housing Act (FEHA), and related regulations are not covered within this version. Responses are limited to California law.` |
| `dashboard_california_disclaimer_bullet_1` | `textarea` | `Does not account for local city/county leave laws, announcement provisions, or other local provisions` |
| `dashboard_california_disclaimer_bullet_2` | `textarea` | `Does not evaluate collective bargaining agreements, institutional policies, or employer-specific practices` |
| `dashboard_california_disclaimer_bullet_3` | `textarea` | `Does not implement required employee obligations such as the interactive process` |
| `dashboard_california_disclaimer_footer` | `textarea` | `Users are responsible for confirming current legal requirements and seeking legal advice when appropriate.` |

---

## Component Design

### 1. Admin Panel Tab Button (`admin/index.php`)

Add a new tab button after the existing "Footer" tab button, before "Colors":

```html
<button class="content-tab" data-tab="dashboard">
    <i class="fas fa-tachometer-alt"></i> Dashboard
</button>
```

### 2. Admin Panel Tab Content (`admin/index.php`)

Add a new `<div class="content-tab-content" id="dashboardContent">` block after the footer tab content. The form uses the existing `content-form`, `step-group`, and `form-group` CSS classes. Structure:

```
dashboardContentForm (id, category=dashboard)
├── Welcome Section (step-group)
│   ├── dashboard_welcome_heading (input[text])
│   └── dashboard_welcome_subheading (textarea)
├── Federal Tool Section (step-group)
│   ├── dashboard_federal_tool_label (input[text])
│   ├── dashboard_federal_disclaimer_heading (input[text])
│   ├── dashboard_federal_disclaimer_intro (textarea, rows=3)
│   ├── dashboard_federal_disclaimer_bullet_1 (textarea, rows=2)
│   ├── dashboard_federal_disclaimer_bullet_2 (textarea, rows=2)
│   ├── dashboard_federal_disclaimer_bullet_3 (textarea, rows=2)
│   └── dashboard_federal_disclaimer_footer (textarea, rows=2)
└── California Tool Section (step-group)
    ├── dashboard_california_tool_label (input[text])
    ├── dashboard_california_disclaimer_heading (input[text])
    ├── dashboard_california_disclaimer_intro (textarea, rows=3)
    ├── dashboard_california_disclaimer_bullet_1 (textarea, rows=2)
    ├── dashboard_california_disclaimer_bullet_2 (textarea, rows=2)
    ├── dashboard_california_disclaimer_bullet_3 (textarea, rows=2)
    └── dashboard_california_disclaimer_footer (textarea, rows=2)
```

Each field has:
- A `<label>` with descriptive text
- A `<small class="form-text">` helper note explaining where it appears
- A live character count: `Character count: <span class="char-count">0</span>`

The form submits via the existing `saveContentForm()` JS function already wired to `api/admin.php` with `action: 'save_content'` and `category: 'dashboard'`.

### 3. Dashboard Renderer (`dashboard.php`)

Replace every hardcoded string with a `getContent()` call. The welcome heading keeps the dynamic user name interpolated in PHP after fetching the static prefix from the store.

```php
// At top of file, after includes:
require_once 'includes/content.php';
initContentSystem();

// In HTML:
<h1><?php echo htmlspecialchars(getContent('dashboard_welcome_heading', 'Welcome back,')); ?> 
    <span id="userWelcomeName"><?php echo htmlspecialchars($user['first_name']); ?></span>
</h1>
<p><?php echo htmlspecialchars(getContent('dashboard_welcome_subheading', 'Choose a compliance tool to generate professional leave responses')); ?></p>

<a href="..." class="tool-button">
    <?php echo htmlspecialchars(getContent('dashboard_federal_tool_label', 'Federal Leave Assistant')); ?>
</a>
<div class="limitation-text">
    <strong><?php echo htmlspecialchars(getContent('dashboard_federal_disclaimer_heading', 'Federal-Specific Limitations')); ?></strong><br>
    <?php echo htmlspecialchars(getContent('dashboard_federal_disclaimer_intro', '...')); ?>
    <br><br>
    <strong>HRLA:</strong>
    <ul>
        <li><?php echo htmlspecialchars(getContent('dashboard_federal_disclaimer_bullet_1', '...')); ?></li>
        <li><?php echo htmlspecialchars(getContent('dashboard_federal_disclaimer_bullet_2', '...')); ?></li>
        <li><?php echo htmlspecialchars(getContent('dashboard_federal_disclaimer_bullet_3', '...')); ?></li>
    </ul>
    <?php echo htmlspecialchars(getContent('dashboard_federal_disclaimer_footer', '...')); ?>
</div>
```

Same pattern for the California tool.

### 4. Content Defaults (`includes/content.php`)

Append the 16 new dashboard keys to the `$defaultContent` array inside `insertDefaultContent()`. All use `INSERT IGNORE` so they only seed on first run.

### 5. Content Fallback (`data/content.json`)

Add all 16 dashboard keys with their default values to `data/content.json` so the file-based fallback path works without a database.

---

## Validation & Security

- The existing `saveContent()` in `api/admin.php` already:
  - Requires admin authentication (HTTP 403 if not admin)
  - Verifies CSRF token (HTTP 403 if invalid)
  - Strips and re-saves values generically per key
- We add client-side validation in the form's submit handler:
  - No field may be empty (matches Requirement 3.1)
  - Character count warning at 800 chars, hard block at 1000 chars (Requirement 6.2)
  - `strip_tags()` is applied server-side in `saveContent()` — we add this explicitly for the dashboard category (Requirement 6.1)

---

## Implementation Tasks

1. Update `includes/content.php` — add 16 dashboard keys to `insertDefaultContent()`
2. Update `data/content.json` — add 16 dashboard keys as fallback defaults
3. Update `admin/index.php` — add Dashboard tab button and tab content form
4. Update `dashboard.php` — replace hardcoded strings with `getContent()` calls, restructure disclaimer bullets as `<ul><li>` elements
