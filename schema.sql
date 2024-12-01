---------------------------------------- DROP STATEMENTS ----------------------------------------

DROP TABLE UserSessions CASCADE CONSTRAINTS;
DROP TABLE StudentAdminUsers CASCADE CONSTRAINTS;
DROP TABLE AdminUsers CASCADE CONSTRAINTS;
DROP TABLE Grad CASCADE CONSTRAINTS;
DROP TABLE UnderGrad CASCADE CONSTRAINTS;
DROP TABLE StudentUsers CASCADE CONSTRAINTS;
DROP TABLE Users CASCADE CONSTRAINTS;

---------------------------------------- TABLES ----------------------------------------

-- Users table (Parent)
CREATE TABLE Users(
    username VARCHAR2(10) PRIMARY KEY,
    first_name VARCHAR2(50) NOT NULL,
    last_name VARCHAR2(50) NOT NULL,
    password VARCHAR2(12) NOT NULL,
    user_status NUMBER(1) CHECK(user_status IN (0, 1, 2)) NOT NULL
    -- 0 -> student, 1 -> admin, 2 -> student-admin
);


-- Student users table
CREATE TABLE StudentUsers(
    student_id VARCHAR2(10) PRIMARY KEY,
    username VARCHAR2(10),
    admission_date DATE NOT NULL,
    address VARCHAR2(50) NOT NULL,
    student_type NUMBER(1) CHECK (student_type IN (0, 1)) NOT NULL,
    -- 0 -> Undergrad, 1 -> Grad
    probation_status CHAR(1) DEFAULT NULL CHECK (probation_status IN ('Y', 'N')),
    -- Y -> in probation, N -> not in probation
    CONSTRAINT fk_student_user FOREIGN KEY (username) REFERENCES Users(username) ON DELETE CASCADE
);

CREATE TABLE UnderGrad(
    student_id VARCHAR2(10) PRIMARY KEY, -- FK from StudentUsers
    standing VARCHAR2(15) CHECK (standing IN ('Freshman', 'Sophomore', 'Junior', 'Senior')) NOT NULL,
    CONSTRAINT fk_undergrad_student FOREIGN KEY (student_id) REFERENCES StudentUsers(student_id) ON DELETE CASCADE
);

CREATE TABLE Grad(
    student_id VARCHAR2(10) PRIMARY KEY, -- FK from StudentUsers
    concentration VARCHAR2(50) NOT NULL,
    CONSTRAINT fk_grad_student FOREIGN KEY (student_id) REFERENCES StudentUsers(student_id) ON DELETE CASCADE
);

-- Admin Users table
CREATE TABLE AdminUsers(
    username VARCHAR2(10) PRIMARY KEY,
    start_date DATE NOT NULL,
    CONSTRAINT fk_admin_user FOREIGN KEY (username) REFERENCES Users(username) ON DELETE CASCADE
);


-- Student Admin Users table
CREATE TABLE StudentAdminUsers(
    username VARCHAR2(10) PRIMARY KEY,
    admission_date DATE NOT NULL,
    start_date DATE NOT NULL,
    CONSTRAINT fk_student_admin_user FOREIGN KEY (username) REFERENCES Users(username) ON DELETE CASCADE
);



-- User Sessions table
CREATE TABLE UserSessions(
    sessionid VARCHAR2(32) PRIMARY KEY,
    sessiondate DATE NOT NULL,
    username VARCHAR2(10),
    CONSTRAINT fk_user_session FOREIGN KEY (username) REFERENCES Users(username) ON DELETE CASCADE
);



---------------------------------------- DATA INSERTION ----------------------------------------

---- USERS table ----
-- Insert into Users table first
INSERT INTO Users (username, first_name, last_name, password, user_status) VALUES
('tedmosby', 'Ted', 'Mosby', 'architect', 0); -- Student
INSERT INTO Users (username, first_name, last_name, password, user_status) VALUES
('marshall', 'Marshall', 'Eriksen', 'lawyer', 0);  -- Student

INSERT INTO Users (username, first_name, last_name, password, user_status) VALUES
('barney', 'Barney', 'Stinson', 'legen', 1);  -- Admin
INSERT INTO Users (username, first_name, last_name, password, user_status) VALUES
('lily', 'Lily', 'Aldrin', 'art', 1);  -- Admin

INSERT INTO Users (username, first_name, last_name, password, user_status) VALUES
('robin', 'Robin', 'Scherbatsky', 'canada', 2);  -- Student-Admin
INSERT INTO Users (username, first_name, last_name, password, user_status) VALUES
('tracy', 'Tracy', 'McConnell', 'bass', 2);  -- Student-Admin

COMMIT;

-- Insert into AdminUsers
INSERT INTO AdminUsers (username, start_date) VALUES ('barney', TO_DATE('2020-07-01', 'YYYY-MM-DD'));
INSERT INTO AdminUsers (username, start_date) VALUES ('lily', TO_DATE('2021-03-15', 'YYYY-MM-DD'));


-- Insert into StudentAdminUsers
INSERT INTO StudentAdminUsers (username, admission_date, start_date) 
VALUES ('robin', TO_DATE('2022-08-15', 'YYYY-MM-DD'), TO_DATE('2022-09-01', 'YYYY-MM-DD'));

INSERT INTO StudentAdminUsers (username, admission_date, start_date) 
VALUES ('tracy', TO_DATE('2021-06-01', 'YYYY-MM-DD'), TO_DATE('2021-07-01', 'YYYY-MM-DD'));


-- Insert into StudentUsers
INSERT INTO StudentUsers (username, admission_date, address, student_type, probation_status) 
VALUES ('tedmosby', TO_DATE('2023-09-01', 'YYYY-MM-DD'), 'West Side Apt', 0, 'N');  -- Undergrad

INSERT INTO StudentUsers (username, admission_date, address, student_type, probation_status) 
VALUES ('marshall', TO_DATE('2023-01-10', 'YYYY-MM-DD'), 'Downtown', 0, 'N');  -- Undergrad

INSERT INTO StudentUsers (username, admission_date, address, student_type, probation_status) 
VALUES ('robin', TO_DATE('2022-08-15', 'YYYY-MM-DD'), 'Upper East Side', 1, 'Y');  -- Grad

INSERT INTO StudentUsers (username, admission_date, address, student_type, probation_status) 
VALUES ('tracy', TO_DATE('2021-06-01', 'YYYY-MM-DD'), 'Brooklyn Heights', 1, 'N');  -- Grad



--- Data is automatically inserted into grad and undergrad


---------------------------------------- TRIGGERS ----------------------------------------
-- DROP TRIGGER IF EXISTS student_id_trigger;
-- DROP TRIGGER IF EXISTS student_type_trigger;

--- Trigger for student id auto generation ---
CREATE OR REPLACE TRIGGER student_id_trigger
BEFORE INSERT ON StudentUsers
FOR EACH ROW
DECLARE
  max_id NUMBER; -- To store the numeric part of the max ID
  new_id NUMBER; -- To store the new numeric ID
  first_initial CHAR(1); -- First letter of first name
  last_initial CHAR(1); -- First letter of last name
BEGIN
  -- Fetch the initials from the Users table based on username
  SELECT SUBSTR(first_name, 1, 1), SUBSTR(last_name, 1, 1)
  INTO first_initial, last_initial
  FROM Users
  WHERE username = :NEW.username;

  -- Get the maximum numeric ID from the existing student_ids
  SELECT NVL(MAX(TO_NUMBER(SUBSTR(student_id, 3))), 0) INTO max_id
  FROM StudentUsers;

  -- Compute the next ID
  new_id := max_id + 1;

  -- Assign the new ID in the format XX12345
  :NEW.student_id := first_initial || last_initial || LPAD(new_id, 5, '0');
END;
/

-- Trigger for automatically updating UnderGrad or Grad table
CREATE OR REPLACE TRIGGER student_type_trigger
AFTER INSERT ON StudentUsers
FOR EACH ROW
BEGIN
  IF :NEW.student_type = 0 THEN
    -- Insert into UnderGrad table for undergrad students
    INSERT INTO UnderGrad (student_id, standing)
    VALUES (:NEW.student_id, 'Freshman'); -- Default standing is 'Freshman'
  ELSIF :NEW.student_type = 1 THEN
    -- Insert into Grad table for grad students
    INSERT INTO Grad (student_id, concentration)
    VALUES (:NEW.student_id, 'Undeclared'); -- Default concentration is 'Undeclared'
  END IF;
END;
/
