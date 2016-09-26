#####  CREATE AND INITIALIZE DATABASE #####


## CREATE DATABASE ('elternsprechtag' is changeable)

CREATE DATABASE elternsprechtag;
USE elternsprechtag;

## CREATE TABLES
CREATE TABLE schueler (
 id                   int  NOT NULL  AUTO_INCREMENT,
 name                 varchar(100)  NOT NULL  ,
 vorname              varchar(100)  NOT NULL  ,
 gebdatum             varchar(10)  NOT NULL  ,
 klasse               varchar(3)    ,
 eid                  int    ,
 CONSTRAINT pk_schueler PRIMARY KEY ( id ),
 CONSTRAINT pk_schueler_0 UNIQUE ( eid ) ,
 CONSTRAINT pk_schueler_1 UNIQUE ( klasse )
) engine=InnoDB;

ALTER TABLE schueler COMMENT '1 zu n verbindung zu ´eltern´';

ALTER TABLE schueler MODIFY gebdatum varchar(10)  NOT NULL   COMMENT 'Geburtsdatum
-> use timstamp?
Format: 01.01.2000';

ALTER TABLE schueler MODIFY klasse varchar(3)     COMMENT '05a - 10c - K2';

ALTER TABLE schueler MODIFY eid int     COMMENT 'ElternID';

CREATE TABLE termin (
 id                   int  NOT NULL  AUTO_INCREMENT,
 slotid               int  NOT NULL  ,
 lid                  int  NOT NULL  ,
 eid                  int    ,
 CONSTRAINT pk_termin PRIMARY KEY ( id ),
 CONSTRAINT pk_termin_0 UNIQUE ( slotid ) ,
 CONSTRAINT pk_termin_1 UNIQUE ( lid ) ,
 CONSTRAINT pk_termin_2 UNIQUE ( eid )
) engine=InnoDB;

ALTER TABLE termin COMMENT 'termin gefüllt mit allen möglichen terminen -> belegt `EID != NULL` else nicht belegt
Kommentar feld? Mitteilung Lehrer -> Eltern zu bestimmten Slot (z.B. kann nur 7 statt 10 min?)';

ALTER TABLE termin MODIFY lid int  NOT NULL   COMMENT 'Lehrer ID';

ALTER TABLE termin MODIFY eid int     COMMENT 'Eltern ID';

CREATE TABLE time_slot (
 id                   int  NOT NULL  AUTO_INCREMENT,
 anfang               timestamp  NOT NULL  ,
 CONSTRAINT pk_time_slot PRIMARY KEY ( id )
) engine=InnoDB;

CREATE TABLE unterricht (
 id                   int  NOT NULL  AUTO_INCREMENT,
 lid                  int  NOT NULL  ,
 klasse               varchar(3)    ,
 CONSTRAINT pk_unterricht PRIMARY KEY ( id ),
 CONSTRAINT pk_unterricht_0 UNIQUE ( lid )
) engine=InnoDB;

CREATE INDEX idx_unterricht ON unterricht ( klasse );

ALTER TABLE unterricht COMMENT 'lehrer <-> klasse';

ALTER TABLE unterricht MODIFY lid int  NOT NULL   COMMENT 'Lehrer ID';

ALTER TABLE unterricht MODIFY klasse varchar(3)     COMMENT '-> siehe ´schueler´';

CREATE TABLE `user` (
 id                   int  NOT NULL  ,
 username             varchar(100)  NOT NULL  ,
 user_type            int   DEFAULT 1 ,
 password_hash        text  NOT NULL  ,
 password_salt        text  NOT NULL  ,
 CONSTRAINT pk_user PRIMARY KEY ( id )
) engine=InnoDB;

ALTER TABLE `user` MODIFY user_type int   DEFAULT 1  COMMENT '0 -> Admin
1 -> Eltern
2 -> Lehrer
default -> 1';

CREATE TABLE eltern (
 id                   int  NOT NULL  AUTO_INCREMENT,
 name                 varchar(100)  NOT NULL  ,
 vorname              varchar(100)  NOT NULL  ,
 userid               int  NOT NULL  ,
 CONSTRAINT pk_eltern PRIMARY KEY ( id )
) engine=InnoDB;

CREATE INDEX idx_eltern ON eltern ( userid );

ALTER TABLE eltern MODIFY vorname varchar(100)  NOT NULL   COMMENT 'name & vorname zusammenfassen?';

CREATE TABLE lehrer (
 id                   int  NOT NULL  AUTO_INCREMENT,
 name                 varchar(100)    ,
 vorname              varchar(100)    ,
 userid               int  NOT NULL  ,
 CONSTRAINT pk_lehrer PRIMARY KEY ( id )
) engine=InnoDB;

CREATE INDEX idx_lehrer ON lehrer ( userid );

ALTER TABLE lehrer COMMENT 'id = Kürzel -> string?
oder Kürzel als eigenes';

ALTER TABLE eltern ADD CONSTRAINT fk_elternchi FOREIGN KEY ( id ) REFERENCES schueler( eid ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE eltern ADD CONSTRAINT fk_eltern FOREIGN KEY ( id ) REFERENCES termin( eid ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE eltern ADD CONSTRAINT fk_eltern_user FOREIGN KEY ( userid ) REFERENCES `user`( id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE lehrer ADD CONSTRAINT fk_lehrer FOREIGN KEY ( id ) REFERENCES unterricht( lid ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE lehrer ADD CONSTRAINT fk_lehrer_0 FOREIGN KEY ( id ) REFERENCES termin( lid ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE lehrer ADD CONSTRAINT fk_lehrer_user FOREIGN KEY ( userid ) REFERENCES `user`( id ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE time_slot ADD CONSTRAINT fk_time_slot FOREIGN KEY ( id ) REFERENCES termin( slotid ) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE unterricht ADD CONSTRAINT fk_unterricht FOREIGN KEY ( klasse ) REFERENCES schueler( klasse ) ON DELETE NO ACTION ON UPDATE NO ACTION;


# Insert Data...
