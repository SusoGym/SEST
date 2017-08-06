CREATE TABLE IF NOT EXISTS `bookable_slot` (
  `id`          INT       NOT NULL,
  `slotid`      INT       NOT NULL,
  `lid`         INT       NOT NULL,
  `eid`         INT,
  `bookingtime` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `eltern` (
  `id`      INT                NOT NULL,
  `name`    VARCHAR(100)
            CHARACTER SET utf8 NOT NULL,
  `vorname` VARCHAR(100)
            CHARACTER SET utf8 NOT NULL,
  `userid`  INT                NOT NULL
);

CREATE TABLE IF NOT EXISTS `lehrer` (
  `id`             INT          NOT NULL,
  `kuerzel`        VARCHAR(5)
                                NOT NULL,
  `untisname`      VARCHAR(100) NOT NULL,
  `name`           VARCHAR(100),
  `vorname`        VARCHAR(100),
  `ldapname`       VARCHAR(100) NOT NULL,
  `deputat`        DOUBLE       NOT NULL,
  `email`          VARCHAR(50),
  `receive_vpmail` TINYINT      NOT NULL DEFAULT '1',
  `receive_news`   TINYINT               DEFAULT '1',
  `upd`            TINYINT(4)   NOT NULL,
  `vpview_all`     TINYINT      NOT NULL DEFAULT '1'
);

CREATE TABLE IF NOT EXISTS `options` (
  `type`      VARCHAR(100) NOT NULL,
  `value`     TEXT         NOT NULL,
  `kommentar` VARCHAR(255) NOT NULL,
  `ordinal`   INT          NOT NULL,
  `field`     TINYINT      NOT NULL
);

CREATE TABLE IF NOT EXISTS `pwd_reset` (
  `token`      VARCHAR(26) NOT NULL,
  `uid`        INT,
  `validuntil` TIMESTAMP   NULL
);

CREATE TABLE IF NOT EXISTS `schueler` (
  `id`       INT          NOT NULL,
  `ASV_ID`   VARCHAR(100) NOT NULL,
  `name`     VARCHAR(100) NOT NULL,
  `vorname`  VARCHAR(100) NOT NULL,
  `gebdatum` VARCHAR(10)  NOT NULL,
  `klasse`   VARCHAR(3)   NOT NULL,
  `kurse`    VARCHAR(100) NOT NULL,
  `eid`      INT,
  `upd`      TINYINT(4)   NOT NULL
);

CREATE TABLE IF NOT EXISTS `termine` (
  `tNr`   INT          NOT NULL,
  `typ`   VARCHAR(200) NOT NULL,
  `start` VARCHAR(16)  NOT NULL,
  `ende`  VARCHAR(16)  NOT NULL,
  `staff` TINYINT(4)   NOT NULL
);

CREATE TABLE IF NOT EXISTS `timetable` (
  `id`      INT        NOT NULL,
  `hour`    INT        NOT NULL,
  `subject` VARCHAR(4),
  `teacher` VARCHAR(4) NOT NULL,
  `room`    VARCHAR(4) NOT NULL,
  `class`   VARCHAR(3) NOT NULL,
  `day`     INT        NOT NULL
);

CREATE TABLE IF NOT EXISTS `time_slot` (
  `id`     INT       NOT NULL,
  `anfang` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ende`   TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00'
);

CREATE TABLE IF NOT EXISTS `unterricht` (
  `id`     INT        NOT NULL,
  `lid`    INT        NOT NULL,
  `klasse` VARCHAR(3) NOT NULL
);

CREATE TABLE IF NOT EXISTS `user` (
  `id`            INT          NOT NULL,
  `user_type`     INT DEFAULT '1',
  `password_hash` TEXT         NOT NULL,
  `email`         VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS `vp_abwesendeLehrer` (
  `alNr`  INT          NOT NULL,
  `datum` VARCHAR(10)  NOT NULL,
  `name`  VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS `vp_blockierteraeume` (
  `brNr`  INT         NOT NULL,
  `datum` VARCHAR(10) NOT NULL,
  `name`  VARCHAR(20) NOT NULL
);

CREATE TABLE IF NOT EXISTS `vp_Protokoll` (
  `pk`        INT          NOT NULL,
  `datum`     DATETIME     NOT NULL,
  `recipient` VARCHAR(100) NOT NULL,
  `mail`      LONGTEXT     NOT NULL,
  `content`   VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS `vp_vpdata` (
  `vnr`       INT          NOT NULL,
  `tag`       INT(3)       NOT NULL,
  `datum`     VARCHAR(10)  NOT NULL,
  `vLehrer`   VARCHAR(40)  NOT NULL,
  `klassen`   VARCHAR(20)  NOT NULL,
  `stunde`    VARCHAR(5)   NOT NULL,
  `fach`      VARCHAR(15)  NOT NULL,
  `raum`      VARCHAR(10)  NOT NULL,
  `eLehrer`   VARCHAR(5)   NOT NULL,
  `eFach`     VARCHAR(15)  NOT NULL,
  `kommentar` VARCHAR(255) NOT NULL,
  `emailed`   VARCHAR(20)  NOT NULL,
  `aktiv`     TINYINT,
  `id`        VARCHAR(100) NOT NULL,
  `stand`     VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS newsletter (
  newsid      INT PRIMARY KEY AUTO_INCREMENT,
  publish     INT,
  text        TEXT,
  schoolyear  TEXT,
  lastchanged TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
);