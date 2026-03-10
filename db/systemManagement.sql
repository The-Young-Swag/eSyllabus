-- ================================================================================================
--  SCHEMA   : Role-Based Access Control (RBAC)
--  DIALECT  : Microsoft SQL Server (T-SQL)
--  VERSION  : 1.0.0
--
--  OVERVIEW
--  --------
--  This schema manages user authentication and menu-level access control.
--  It is built around four core entities:
--
--    roles       → What kind of user is this? (e.g. Admin, Editor, Viewer)
--    menus       → What navigational items exist in the application?
--    privileges  → Which roles are allowed to see which menus?
--    users       → Who is logging in, and what role do they have?
--
--  AUTOMATION
--  ----------
--  Two triggers eliminate the need for manual privilege setup:
--    - Adding a new ROLE  → automatically seeds a denied privilege for every existing menu.
--    - Adding a new MENU  → automatically seeds a denied privilege for every existing role.
--  This ensures no role-menu combination is ever missing from the privileges table.
--
--  SECURITY PHILOSOPHY (Negative Space Programming)
--  --------------------------------------------------
--  Access is DENIED by default. A privilege row is seeded with is_active = 0 (no access)
--  and must be explicitly activated. The system is secure in its resting state —
--  nothing is accessible unless it is intentionally granted.
--
--  NAMING CONVENTIONS
--  ------------------
--  All constraints and indexes follow a fully readable naming pattern:
--    primarykey_{table}
--    unique_{table}_{column}
--    foreignkey_{table}_{referenced_table}
--    default_{table}_{column}
--    index_{table}_{column(s)}
--
--  EXECUTION ORDER
--  ---------------
--  Run this script top-to-bottom in a single transaction.
--  Tables must be created before triggers and indexes that depend on them.
-- ================================================================================================
-- ================================================================================================
--  TABLE : roles
--
--  Defines the roles that can be assigned to users.
--  A role represents a permission tier or job function (e.g. "Administrator", "Read-Only").
--  Each role will automatically receive a denied privilege entry for every menu that exists,
--  managed by the trigger [ trg_roles_auto_seed_privileges ] defined further below.
--
--  COLUMNS
--  -------
--  role_id    → Auto-incrementing surrogate primary key. Never reassigned or reused.
--  role_name  → Human-readable label for the role. Must be unique across the system.
--  is_active  → Soft-delete flag. Inactive roles still exist for audit history but
--               should be excluded from login and privilege resolution queries.
-- ================================================================================================
CREATE TABLE
    roles (
        role_id INT IDENTITY (1, 1) NOT NULL,
        role_name NVARCHAR (100) NOT NULL,
        is_active BIT NOT NULL CONSTRAINT default_roles_is_active DEFAULT 1,
        CONSTRAINT primarykey_roles PRIMARY KEY (role_id),
        CONSTRAINT unique_roles_role_name UNIQUE (role_name)
    );

GO
-- ================================================================================================
--  TABLE : menus
--
--  Defines the navigational menu structure for the application.
--  Supports unlimited parent-child nesting via a self-referencing foreign key on mother_menu.
--
--  HIERARCHY MODEL
--  ---------------
--  A menu is a ROOT (mother) menu when  mother_menu IS NULL.
--  A menu is a CHILD menu         when  mother_menu = <parent menu_id>.
--
--  Example:
--    menu_id: 1, menu_name: "Settings",  mother_menu: NULL       <- root menu
--    menu_id: 2, menu_name: "User Mgmt", mother_menu: 1          <- child of Settings
--    menu_id: 3, menu_name: "Role Mgmt", mother_menu: 1          <- child of Settings
--
--  COLUMNS
--  -------
--  menu_id     → Auto-incrementing surrogate primary key.
--  menu_name   → Display label rendered in the navigation UI.
--  mother_menu → FK to self. NULL means this is a root menu. References menu_id of the parent.
--  menu_code   → Short machine-readable identifier (e.g. "MENU_SETTINGS"). Useful for
--               programmatic lookups without relying on names or IDs.
--  menu_link   → Relative URL the menu item points to (e.g. "userManagement.php").
--               NULL for parent menus that serve only as grouping containers.
--  menu_order  → Controls the render order of sibling menus at the same hierarchy level.
--               Lower values appear first. Defaults to 0.
--  icon        → CSS class string for the menu icon (e.g. "nav-icon fas fa-solid fa-users").
--  is_active   → Soft-delete flag. Inactive menus are hidden from navigation.
-- ================================================================================================
CREATE TABLE
    menus (
        menu_id INT IDENTITY (1, 1) NOT NULL,
        menu_name NVARCHAR (150) NOT NULL,
        mother_menu INT NULL,
        menu_code NVARCHAR (100) NULL,
        menu_link NVARCHAR (255) NULL,
        menu_order INT NOT NULL CONSTRAINT default_menus_menu_order DEFAULT 0,
        icon NVARCHAR (255) NULL,
        is_active BIT NOT NULL CONSTRAINT default_menus_is_active DEFAULT 1,
        CONSTRAINT primarykey_menus PRIMARY KEY (menu_id),
        CONSTRAINT unique_menus_menu_code UNIQUE (menu_code),
        CONSTRAINT foreignkey_menus_parent_menu FOREIGN KEY (mother_menu) REFERENCES menus (menu_id)
    );

GO
-- ================================================================================================
--  TABLE : privileges
--
--  Junction table that maps every role to every menu, controlling access per combination.
--
--  DESIGN INTENT (Negative Space)
--  --------------------------------
--  This table is never left incomplete. Every role-menu combination must have exactly one row.
--  Two triggers maintain this guarantee automatically:
--    - When a role  is added → a denied row is seeded for every existing menu.
--    - When a menu  is added → a denied row is seeded for every existing role.
--  Access is DENIED (is_active = 0) by default and must be explicitly activated.
--  This means the system is secure even if triggers or seeding logic has gaps.
--
--  COLUMNS
--  -------
--  privilege_id → Auto-incrementing surrogate primary key.
--  role_id      → FK to roles. Identifies which role this privilege belongs to.
--  menu_id      → FK to menus. Identifies which menu this privilege governs.
--  is_active    → 1 = this role CAN access this menu. 0 = access denied (default).
--
--  CASCADE BEHAVIOR
--  ----------------
--  Deleting a role or menu automatically removes all associated privilege rows,
--  preventing orphaned records.
-- ================================================================================================
CREATE TABLE
    privileges (
        privilege_id INT NOT NULL IDENTITY (1, 1),
        role_id INT NOT NULL,
        menu_id INT NOT NULL,
        is_active BIT NOT NULL CONSTRAINT default_privileges_is_active DEFAULT 0,
        CONSTRAINT primarykey_privileges PRIMARY KEY (privilege_id),
        CONSTRAINT unique_privileges_role_menu UNIQUE (role_id, menu_id),
        CONSTRAINT foreignkey_privileges_roles FOREIGN KEY (role_id) REFERENCES roles (role_id) ON DELETE CASCADE,
        CONSTRAINT foreignkey_privileges_menus FOREIGN KEY (menu_id) REFERENCES menus (menu_id) ON DELETE CASCADE
    );

GO
-- ================================================================================================
--  TABLE : users
--
--  Stores registered application users and their assigned role.
--  Each user belongs to exactly one role, which governs their menu access via the
--  privileges table.
--
--  SECURITY REQUIREMENT
--  --------------------
--  The [ password ] column MUST store a securely hashed value only.
--  Acceptable algorithms: bcrypt, Argon2, or PBKDF2 with sufficient cost factor.
--  Plain-text or weakly hashed (MD5, SHA1) passwords must never be stored.
--
--  COLUMNS
--  -------
--  user_id   → Auto-incrementing surrogate primary key.
--  username  → Unique login handle. Case-sensitivity depends on collation settings.
--  email     → Optional unique contact email. NULL permitted for system accounts.
--  password  → Hashed credential string. See security requirement above.
--  role_id   → FK to roles. Determines the user's access privileges.
--  is_active → Soft-delete flag. Deactivated users cannot log in but are preserved
--              for audit history and data integrity.
-- ================================================================================================
CREATE TABLE
    users (
        user_id INT IDENTITY (1, 1) NOT NULL,
        username NVARCHAR (100) NOT NULL,
        email NVARCHAR (150) NULL,
        password NVARCHAR (255) NOT NULL,
        role_id INT NOT NULL,
        is_active BIT NOT NULL CONSTRAINT default_users_is_active DEFAULT 1,
        CONSTRAINT primarykey_users PRIMARY KEY (user_id),
        CONSTRAINT unique_users_username UNIQUE (username),
        CONSTRAINT unique_users_email UNIQUE (email),
        CONSTRAINT foreignkey_users_roles FOREIGN KEY (role_id) REFERENCES roles (role_id)
    );

GO
-- ================================================================================================
--  TRIGGER : trg_roles_auto_seed_privileges
--  FIRES ON : roles — AFTER INSERT
--
--  PURPOSE
--  -------
--  When one or more new roles are inserted, this trigger automatically creates a denied
--  privilege row (is_active = 0) for every menu that currently exists in the system.
--  This guarantees that every role has a complete privilege map immediately upon creation,
--  with no manual seeding required.
--
--  BATCH-SAFE
--  ----------
--  Written in set-based T-SQL. Handles multi-row inserts correctly (e.g. INSERT INTO roles
--  SELECT ... from another table). Does NOT assume a single row in [ inserted ].
--
--  IDEMPOTENCY (Negative Space Guard)
--  ------------------------------------
--  The WHERE NOT EXISTS clause prevents duplicate privilege rows if this trigger
--  is ever fired again for the same role-menu pair (e.g. after a script re-run or
--  data migration). This makes the trigger safe to run in any environment.
--
--  PERFORMANCE
--  -----------
--  SET NOCOUNT ON suppresses row-count messages, avoiding unnecessary network overhead.
--  Early exit via RETURN when [ inserted ] is empty avoids any wasted processing.
-- ================================================================================================
CREATE TRIGGER trg_roles_auto_seed_privileges ON roles AFTER INSERT AS BEGIN
SET
    NOCOUNT ON;

-- Negative Space: Exit immediately if no rows were inserted.
-- This prevents any processing overhead on empty trigger invocations.
IF NOT EXISTS (
    SELECT
        1
    FROM
        inserted
) RETURN;

-- For each newly inserted role, create one denied privilege row per existing menu.
-- Access starts as denied (is_active = 0) and must be granted explicitly.
INSERT INTO
    privileges (role_id, menu_id, is_active)
SELECT
    inserted_role.role_id,
    existing_menu.menu_id,
    0 -- Denied by default. Must be activated manually.
FROM
    inserted AS inserted_role
    CROSS JOIN menus AS existing_menu
    -- Negative Space: Skip pairs that already exist to remain idempotent.
WHERE
    NOT EXISTS (
        SELECT
            1
        FROM
            privileges AS existing_privilege
        WHERE
            existing_privilege.role_id = inserted_role.role_id
            AND existing_privilege.menu_id = existing_menu.menu_id
    );

END;

GO
-- ================================================================================================
--  TRIGGER : trg_menus_auto_seed_privileges
--  FIRES ON : menus — AFTER INSERT
--
--  PURPOSE
--  -------
--  When one or more new menus are inserted, this trigger automatically creates a denied
--  privilege row (is_active = 0) for every role that currently exists in the system.
--  This guarantees that every role immediately has a privilege entry for the new menu,
--  with no manual setup required.
--
--  BATCH-SAFE
--  ----------
--  Written in set-based T-SQL. Handles multi-row inserts correctly. Does NOT assume
--  a single row in [ inserted ].
--
--  IDEMPOTENCY (Negative Space Guard)
--  ------------------------------------
--  The WHERE NOT EXISTS clause prevents duplicate rows if the trigger fires more than
--  once for the same role-menu combination.
--
--  PERFORMANCE
--  -----------
--  SET NOCOUNT ON suppresses row-count messages.
--  Early exit via RETURN when [ inserted ] is empty avoids wasted processing.
-- ================================================================================================
CREATE TRIGGER trg_menus_auto_seed_privileges ON menus AFTER INSERT AS BEGIN
SET
    NOCOUNT ON;

-- Negative Space: Exit immediately if no rows were inserted.
IF NOT EXISTS (
    SELECT
        1
    FROM
        inserted
) RETURN;

-- For each newly inserted menu, create one denied privilege row per existing role.
-- Access starts as denied (is_active = 0) and must be granted explicitly.
INSERT INTO
    privileges (role_id, menu_id, is_active)
SELECT
    existing_role.role_id,
    inserted_menu.menu_id,
    0 -- Denied by default. Must be activated manually.
FROM
    roles AS existing_role
    CROSS JOIN inserted AS inserted_menu
    -- Negative Space: Skip pairs that already exist to remain idempotent.
WHERE
    NOT EXISTS (
        SELECT
            1
        FROM
            privileges AS existing_privilege
        WHERE
            existing_privilege.role_id = existing_role.role_id
            AND existing_privilege.menu_id = inserted_menu.menu_id
    );

END;

GO
-- ================================================================================================
--  INDEXES
--
--  PURPOSE
--  -------
--  These indexes are tuned for the most frequent, performance-critical query patterns
--  in an RBAC system. Each is a covering index — the INCLUDE columns satisfy common
--  SELECT projections without requiring a lookup back to the base table (no key lookup).
--
--  INDEX SUMMARY
--  -------------
--  index_privileges_role_id
--    → "What menus can this role access?" (called on every page load for navigation rendering)
--
--  index_privileges_menu_id
--    → "Which roles have access to this menu?" (used in privilege management screens)
--
--  index_users_role_id
--    → "Which users belong to this role?" (used in admin user-role listing)
--
--  index_menus_mother_menu
--    → "What child menus belong to this parent?" (used when building hierarchical nav trees)
--
--  index_menus_active_order
--    → "Give me all active menus in order." (used for full nav tree rendering on login)
-- ================================================================================================
-- Covers access-check queries: given a role, find all accessible menus
CREATE INDEX index_privileges_role_id ON privileges (role_id) INCLUDE (menu_id, is_active);

-- Covers privilege management queries: given a menu, find all role mappings
CREATE INDEX index_privileges_menu_id ON privileges (menu_id) INCLUDE (role_id, is_active);

-- Covers admin queries: given a role, list all users assigned to it
CREATE INDEX index_users_role_id ON users (role_id) INCLUDE (username, is_active);

-- Covers hierarchy traversal: given a parent menu, find all direct children
CREATE INDEX index_menus_mother_menu ON menus (mother_menu) INCLUDE (menu_id, menu_name, menu_order, is_active);

-- Covers full nav tree rendering: fetch all active menus in display order
CREATE INDEX index_menus_active_order ON menus (is_active, menu_order) INCLUDE (menu_id, menu_name, mother_menu, menu_link, icon);

GO