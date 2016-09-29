#####  CREATE AND INITIALIZE DATABASE #####


## CREATE DATABASE ('elternsprechtag' is changeable)

CREATE DATABASE elternsprechtag;
USE elternsprechtag;

## CREATE TABLES
CREATE TABLE schueler (
 id                   int  PRIMARY KEY NOT NULL  AUTO_INCREMENT,
 name                 varchar(100)  NOT NULL  ,
 vorname              varchar(100)  NOT NULL  ,
 gebdatum             varchar(10)  NOT NULL  ,
 klasse               varchar(3)    ,
 eid                  int
) engine=InnoDB;

CREATE TABLE termin (
 id                   int  PRIMARY KEY NOT NULL  AUTO_INCREMENT,
 slotid               int  UNIQUE NOT NULL  ,
 lid                  int  NOT NULL  ,
 eid                  int
) engine=InnoDB;

CREATE TABLE time_slot (
 id                   int  PRIMARY KEY NOT NULL  AUTO_INCREMENT,
 anfang               timestamp  NOT NULL
) engine=InnoDB;

CREATE TABLE unterricht (
 id                   int  PRIMARY KEY NOT NULL  AUTO_INCREMENT,
 lid                  int  NOT NULL  ,
 klasse               varchar(3)
) engine=InnoDB;

CREATE TABLE `user` (
 id                   int PRIMARY KEY NOT NULL  ,
 username             varchar(100) UNIQUE NOT NULL  ,
 user_type            int   DEFAULT 1 ,
 password_hash        text  NOT NULL  ,
 password_salt        text  NOT NULL
) engine=InnoDB;

CREATE TABLE eltern (
 id                   int  PRIMARY KEY NOT NULL  AUTO_INCREMENT,
 name                 varchar(100)  NOT NULL  ,
 vorname              varchar(100)  NOT NULL  ,
 userid               int  NOT NULL UNIQUE
) engine=InnoDB;

CREATE TABLE lehrer (
 id                   int PRIMARY KEY NOT NULL  AUTO_INCREMENT,
 name                 varchar(100)    ,
 vorname              varchar(100)    ,
 kuerzel              varchar(5) UNIQUE,
 email                varchar(50) UNIQUE,
 userid               int NOT NULL DEFAULT -1
) engine=InnoDB;

# Add comments

ALTER TABLE lehrer                                        COMMENT 'id = Kürzel -> string? oder Kürzel als eigenes';
ALTER TABLE schueler                                      COMMENT '1 zu n verbindung zu ´eltern´';
ALTER TABLE schueler MODIFY gebdatum varchar(10) NOT NULL COMMENT 'Geburtsdatum -> use timstamp? Format: 01.01.2000';
ALTER TABLE schueler MODIFY klasse varchar(3)             COMMENT '05a - 10c - K2';
ALTER TABLE schueler MODIFY eid int                       COMMENT 'ElternID';
ALTER TABLE termin                                        COMMENT 'termin gefüllt mit allen möglichen terminen -> belegt `EID != NULL` else nicht belegt Kommentar feld? Mitteilung Lehrer -> Eltern zu bestimmten Slot (z.B. kann nur 7 statt 10 min?)';
ALTER TABLE termin MODIFY lid int  NOT NULL               COMMENT 'Lehrer ID';
ALTER TABLE termin MODIFY eid int                         COMMENT 'Eltern ID';
ALTER TABLE unterricht                                    COMMENT 'lehrer <-> klasse';
ALTER TABLE unterricht MODIFY lid int  NOT NULL           COMMENT 'Lehrer ID';
ALTER TABLE unterricht MODIFY klasse varchar(3)           COMMENT '-> siehe ´schueler´';
ALTER TABLE `user` MODIFY user_type int   DEFAULT 1       COMMENT '0 -> Admin; 1 -> Eltern; 2 -> Lehrer;   default -> 1';
ALTER TABLE eltern MODIFY vorname varchar(100)  NOT NULL  COMMENT 'name & vorname zusammenfassen?';

# Insert Data...