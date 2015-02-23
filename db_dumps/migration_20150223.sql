DROP INDEX UNIQ_CDD78530C44967C5 ON `tracking_agent`;
ALTER TABLE `tracking_agent` ADD `hash` VARCHAR(32) NOT NULL AFTER `user_agent`, CHANGE `user_agent` `user_agent` TEXT NOT NULL;
UPDATE `tracking_agent` SET `hash` = MD5(`user_agent`);
CREATE UNIQUE INDEX UNIQ_CDD78530D1B862B8 ON `tracking_agent` (`hash`);
ALTER TABLE `tracking` ADD `timestamp_created` INT NOT NULL;
UPDATE `tracking` SET `timestamp_created` = UNIX_TIMESTAMP( `time_created` );
