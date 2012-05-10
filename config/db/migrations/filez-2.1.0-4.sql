ALTER TABLE fz_user ALTER COLUMN quota SET DEFAULT '2G';

INSERT INTO `fz_info` (`key`, `value`) VALUES ('db_version', '2.1.0-4');