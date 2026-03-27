-- ─────────────────────────────────────────────────────────────────
-- studentData
--
-- PK         : id  (surrogate, auto-increment)
--              studNumber alone is NOT unique — ARS produces duplicates.
--
-- Uniqueness : (studNumber, name)  ← same composite key used by the
--              PHP upsert in syncAPIToDatabase.php
--
-- studNumber : varchar, not int.
--              API returns values like "GS2021001" and "2022100114" —
--              mixed alpha-numeric, so int would reject the first form.
--
-- sex        : varchar(6) minimum — "Female" is 6 chars; varchar(5)
--              would silently truncate on some collations.
--
-- birthDate  : stored as the API's MM/DD/YYYY string (varchar(10)).
--              Not converted to DATE so no format mismatch can occur
--              and the secretKey comparison in PHP stays a plain string op.
-- ─────────────────────────────────────────────────────────────────
CREATE TABLE
    studentData (
        id INT IDENTITY (1, 1) NOT NULL, -- surrogate PK
        studNumber VARCHAR(20) NOT NULL, -- e.g. "GS2021001", "2022100114"
        name NVARCHAR (255) NOT NULL,
        sex VARCHAR(6) NULL, -- "Male" / "Female"
        college VARCHAR(20) NULL, -- e.g. "CET", "GS", "CEd"
        course VARCHAR(20) NULL, -- e.g. "BSIT", "Ph.D.", "MAED"
        enrollment_status VARCHAR(20) NULL, -- "STUDENT" / "GUEST"
        birthDate VARCHAR(10) NULL, -- "MM/DD/YYYY"
        CONSTRAINT PK_studentData PRIMARY KEY (id),
        CONSTRAINT UQ_studentData_num_name UNIQUE (studNumber, name)
    );

-- ─────────────────────────────────────────────────────────────────
-- employeeData
--
-- PK         : id  (surrogate, auto-increment)
--
-- Uniqueness : empNumber  ← treated as unique for employees.
--              Covers both "TAU-###" and "JO-###" prefixes.
--
-- empNumber  : varchar, not int.
--              Values like "TAU-054" and "JO-004" are strings.
-- ─────────────────────────────────────────────────────────────────
CREATE TABLE
    employeeData (
        id INT IDENTITY (1, 1) NOT NULL, -- surrogate PK
        empNumber NVARCHAR (20) NOT NULL, -- e.g. "TAU-054", "JO-004"
        name VARCHAR(255) NOT NULL,
        sex VARCHAR(6) NULL, -- "Male" / "Female"
        CONSTRAINT PK_employeeData PRIMARY KEY (id),
        CONSTRAINT UQ_employeeData_number UNIQUE (empNumber)
    );

-- ─────────────────────────────────────────────────────────────────
-- Optional indexes — add if you query these columns frequently
-- ─────────────────────────────────────────────────────────────────
-- Speeds up resolveUserFromDatabase() which filters by studNumber
CREATE INDEX IX_studentData_studNumber ON studentData (studNumber);

-- Speeds up resolveUserFromDatabase() which filters by empNumber
CREATE INDEX IX_employeeData_empNumber ON employeeData (empNumber);