# Requirements Document

## Introduction

The Dashboard Content Management feature extends the existing admin content management system to cover the user-facing dashboard (`dashboard.php`). Currently, admins can edit homepage and marketing content via the admin panel, but the dashboard's editable text — including the welcome heading, tool selection labels, limitation/disclaimer text blocks, and bullet points — is hardcoded in PHP. This feature allows admins to edit all dashboard text content through the admin panel via a new "Dashboard" tab in the existing Content Management section, with changes persisted to the `site_content` database table (with `data/content.json` as fallback) and reflected live for all authenticated users.

## Glossary

- **Admin**: An authenticated user with `is_admin = 1` or `access_level = 'administrator'` who has access to the admin panel at `admin/index.php`.
- **Dashboard**: The authenticated user-facing page at `dashboard.php` that presents the welcome message and tool selection interface.
- **Content_Manager**: The admin panel subsystem responsible for reading and writing editable content values.
- **Content_Store**: The `site_content` MySQL table, with `data/content.json` as a file-based fallback, used to persist content values.
- **Dashboard_Renderer**: The `dashboard.php` page that reads content values and renders them for authenticated users.
- **Content_Key**: A unique string identifier (e.g., `dashboard_welcome_heading`) used to retrieve a specific content value from the Content_Store.
- **Content_Value**: The text string associated with a Content_Key, displayed to users on the Dashboard.
- **Admin_Panel**: The interface at `admin/index.php` where admins manage site configuration and content.
- **Disclaimer Block**: The limitation/disclaimer text displayed beneath each tool button on the dashboard, including the heading, intro sentence, and bullet points.

---

## Requirements

### Requirement 1: Dashboard Content Keys in Content Store

**User Story:** As an admin, I want all editable dashboard text to be stored in the Content_Store, so that I have a single source of truth for dashboard content.

#### Acceptance Criteria

1. THE Content_Store SHALL contain a `dashboard` category with Content_Keys covering all editable dashboard sections:
   - **Welcome section**: `dashboard_welcome_heading`, `dashboard_welcome_subheading`
   - **Federal tool**: `dashboard_federal_tool_label`, `dashboard_federal_disclaimer_heading`, `dashboard_federal_disclaimer_intro`, `dashboard_federal_disclaimer_bullet_1`, `dashboard_federal_disclaimer_bullet_2`, `dashboard_federal_disclaimer_bullet_3`, `dashboard_federal_disclaimer_footer`
   - **California tool**: `dashboard_california_tool_label`, `dashboard_california_disclaimer_heading`, `dashboard_california_disclaimer_intro`, `dashboard_california_disclaimer_bullet_1`, `dashboard_california_disclaimer_bullet_2`, `dashboard_california_disclaimer_bullet_3`, `dashboard_california_disclaimer_footer`
2. THE Content_Store SHALL provide a default Content_Value for each dashboard Content_Key matching the current hardcoded text in `dashboard.php`, so that the Dashboard renders identically before any admin edits.
3. WHEN the `site_content` table does not exist or is unavailable, THE Content_Manager SHALL fall back to reading dashboard content from `data/content.json`.
4. WHEN a dashboard Content_Key is not found in the Content_Store, THE Dashboard_Renderer SHALL display the hardcoded default value for that key.

---

### Requirement 2: Admin Panel — Dashboard Content Editing UI

**User Story:** As an admin, I want a dedicated "Dashboard" tab in the Content Management section of the admin panel, so that I can edit all dashboard text in one place.

#### Acceptance Criteria

1. WHEN an admin navigates to the Content Management section of the Admin_Panel, THE Admin_Panel SHALL display a "Dashboard" tab alongside the existing content tabs (e.g., Hero, Features, Pricing, etc.).
2. WHEN the admin selects the Dashboard tab, THE Admin_Panel SHALL display a form with labeled input fields grouped into logical sections: Welcome, Federal Tool, California Tool.
3. THE Admin_Panel SHALL use `<input type="text">` for short fields (labels, headings) and `<textarea>` for longer fields (intro sentences, bullet points, footer notes).
4. THE Admin_Panel SHALL display each input field with a descriptive label and helper text indicating where the content appears on the Dashboard.
5. THE Admin_Panel SHALL show a live character count on each field, with a visual warning when the count approaches or exceeds 1000 characters.
6. THE Admin_Panel SHALL pre-populate each input field with the current Content_Value retrieved from the Content_Store.

---

### Requirement 3: Saving Dashboard Content

**User Story:** As an admin, I want to save changes to dashboard content, so that updated text is immediately available to users.

#### Acceptance Criteria

1. WHEN an admin submits the dashboard content form, THE Content_Manager SHALL validate that no submitted Content_Value is empty.
2. IF a submitted Content_Value is empty, THEN THE Content_Manager SHALL reject the save request and return a descriptive error message identifying the empty field.
3. WHEN all submitted Content_Values are valid, THE Content_Manager SHALL persist each value to the Content_Store under its corresponding Content_Key.
4. WHEN the Content_Store save succeeds, THE Content_Manager SHALL return a success confirmation to the Admin_Panel.
5. IF the Content_Store save fails, THEN THE Content_Manager SHALL attempt to save to `data/content.json` as a fallback and notify the admin of the degraded save mode.
6. WHEN the admin saves dashboard content, THE Content_Manager SHALL record the `updated_at` timestamp and `updated_by` admin user ID in the Content_Store.

---

### Requirement 4: Live Rendering of Dashboard Content

**User Story:** As an authenticated user, I want the dashboard to display the latest admin-managed content, so that I always see current and accurate information.

#### Acceptance Criteria

1. WHEN an authenticated user loads the Dashboard, THE Dashboard_Renderer SHALL retrieve each dashboard Content_Value from the Content_Store using the corresponding Content_Key.
2. WHEN a Content_Value has been updated by an admin, THE Dashboard_Renderer SHALL display the updated Content_Value on the next page load without requiring a code deployment.
3. WHEN the Content_Store is unavailable, THE Dashboard_Renderer SHALL display the hardcoded default Content_Value so that the Dashboard remains functional.
4. THE Dashboard_Renderer SHALL sanitize all Content_Values using `htmlspecialchars()` before rendering them in HTML output.
5. THE Dashboard_Renderer SHALL render disclaimer bullet points as individual `<li>` elements, each sourced from its own Content_Key.

---

### Requirement 5: Access Control for Dashboard Content Editing

**User Story:** As a system owner, I want only admins to be able to edit dashboard content, so that unauthorized users cannot alter what authenticated users see.

#### Acceptance Criteria

1. WHEN a non-admin user attempts to access the admin content management API endpoint, THE Content_Manager SHALL reject the request with an HTTP 403 response.
2. WHEN an unauthenticated request is made to the admin content management API endpoint, THE Content_Manager SHALL reject the request with an HTTP 401 response.
3. THE Admin_Panel SHALL require a valid CSRF token on all dashboard content save requests.
4. IF a dashboard content save request is submitted without a valid CSRF token, THEN THE Content_Manager SHALL reject the request and return an HTTP 403 response.

---

### Requirement 6: Content Validation

**User Story:** As an admin, I want the system to validate dashboard content before saving, so that I don't accidentally publish malformed or excessively long content.

#### Acceptance Criteria

1. WHEN a submitted Content_Value contains HTML tags, THE Content_Manager SHALL strip all HTML tags before persisting the value.
2. WHEN a submitted Content_Value exceeds 1000 characters, THE Content_Manager SHALL reject the value and return an error message specifying the field name and character limit.
3. WHEN all Content_Values pass validation, THE Content_Manager SHALL proceed with persisting the values to the Content_Store.
