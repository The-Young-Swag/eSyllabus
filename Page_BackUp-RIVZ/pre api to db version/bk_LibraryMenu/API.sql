CREATE TABLE
    studentData (
        studNumber int constraint,
        name varchar(255) constraint,
        sex varchar(5) constraint,
        college varchar(20) constraint,
        course varchar(20) constraint,
        enrollment_status varchar(20) constraint,
        birthDate varchar(10) constraint,
    );

CREATE TABLE
    employeeData (
        empNumber int constraint,
        name varchar(255) constraint,
        sex varchar(5) constraint,
    );