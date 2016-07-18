
 #####  CREATE AND INITIALIZE DATABASE #####


## CREATE DATABASE ('elternsprechtag' is changeable)

CREATE DATABASE elternsprechtag;
USE elternsprechtag;

## CREATE TABLES

CREATE TABLE schueler ( 
	id                   int  NOT NULL            PRIMARY KEY,
	name                 varchar(100)  NOT NULL  ,
	vorname              varchar(100)  NOT NULL  ,
	gebdatum             varchar(10)  NOT NULL  ,
	klasse               varchar(3)    ,
	eid                  int    
 ) engine=InnoDB;

ALTER TABLE schueler COMMENT '1 zu n verbindung zu ´eltern´';

ALTER TABLE schueler MODIFY gebdatum varchar(10)  NOT NULL   COMMENT 'Geburtsdatum
 -> use timstamp?
Format: 01.01.2000';

ALTER TABLE schueler MODIFY klasse varchar(3)     COMMENT '05a - 10c - K2';

ALTER TABLE schueler MODIFY eid int     COMMENT 'ElternID';

CREATE TABLE termin ( 
	id                   int  NOT NULL  AUTO_INCREMENT PRIMARY KEY,
	slotid               int  NOT NULL  ,
	lid                  int  NOT NULL  ,
	eid                  int  NOT NULL
 ) engine=InnoDB;

ALTER TABLE termin COMMENT 'termin gefüllt mit allen möglichen terminen -> belegt `EID != NULL` else nicht belegt
Kommentar feld? Mitteilung Lehrer -> Eltern zu bestimmten Slot (z.B. kann nur 7 statt 10 min?)';

ALTER TABLE termin MODIFY lid int  NOT NULL   COMMENT 'Lehrer ID';

ALTER TABLE termin MODIFY eid int     COMMENT 'Eltern ID';

CREATE TABLE time_slot ( 
	id                   int  NOT NULL  AUTO_INCREMENT PRIMARY KEY,
	anfang               timestamp  NOT NULL 
 ) engine=InnoDB;

CREATE TABLE unterricht ( 
	id                   int  NOT NULL  AUTO_INCREMENT PRIMARY KEY,
	lid                  int  NOT NULL  ,
	klasse               varchar(3)  
 ) engine=InnoDB;

ALTER TABLE unterricht COMMENT 'lehrer <-> klasse';

ALTER TABLE unterricht MODIFY lid int  NOT NULL   COMMENT 'Lehrer ID';

ALTER TABLE unterricht MODIFY klasse varchar(3)     COMMENT '-> siehe ´schueler´';

CREATE TABLE eltern ( 
	id                   int  NOT NULL  AUTO_INCREMENT PRIMARY KEY,
	name                 varchar(100)  NOT NULL  ,
	vorname              varchar(100)  NOT NULL 
 ) engine=InnoDB;

ALTER TABLE eltern MODIFY vorname varchar(100)  NOT NULL   COMMENT 'name & vorname zusammenfassen?';

CREATE TABLE lehrer ( 
	id                   int  NOT NULL  AUTO_INCREMENT PRIMARY KEY,
	name                 varchar(100)    ,
	vorname              varchar(100)    ,
	kuerzel              varchar(3) NOT NULL UNIQUE,
	email				 varchar(100) NOT NULL UNIQUE
 ) engine=InnoDB;

ALTER TABLE lehrer COMMENT 'id = Kürzel -> string?
oder Kürzel als eigenes';


 #####    INSERT DATA #####
 
 # ...
