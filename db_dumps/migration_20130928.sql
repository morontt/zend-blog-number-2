ALTER TABLE `comments` ADD `disqus_id` INT DEFAULT NULL;
ALTER TABLE `posts` ADD `disqus_thread` INT DEFAULT NULL;
ALTER TABLE `commentators` ADD `disqus_id` INT DEFAULT NULL, ADD `email_hash` VARCHAR(32) DEFAULT NULL;
ALTER TABLE `users` ADD `email_hash` VARCHAR(32) DEFAULT NULL;
UPDATE `users` SET `email_hash` = MD5(`mail`);