CREATE TABLE IF NOT EXISTS CONSOLR_BIRTHDAY (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  birth_date date DEFAULT NULL,
  tumblr_name VARCHAR( 255 ) NOT NULL ,
  PRIMARY KEY (id),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB;

CREATE VIEW CONSOLR_VW_MISSING_BIRTHDAYS AS
    select distinct t.TAG AS name from CONSOLR_POST_TAG t
    where ((t.SHOW_ORDER = 1)
        and (t.TUMBLR_NAME = '')
        and (not(t.TAG in (select CONSOLR_BIRTHDAY.name from CONSOLR_BIRTHDAY)))
        and (t.TAG not in ('art')))
    order by t.TAG