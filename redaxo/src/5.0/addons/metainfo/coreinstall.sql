ALTER TABLE `%TABLE_PREFIX%article` ADD `art_online_from` TEXT;
ALTER TABLE `%TABLE_PREFIX%article` ADD `art_online_to` TEXT;
ALTER TABLE `%TABLE_PREFIX%article` ADD `art_description` TEXT;
ALTER TABLE `%TABLE_PREFIX%article` ADD `art_keywords` TEXT;
ALTER TABLE `%TABLE_PREFIX%article` ADD `art_file` VARCHAR(255);
ALTER TABLE `%TABLE_PREFIX%article` ADD `art_teaser` VARCHAR(255);
ALTER TABLE `%TABLE_PREFIX%article` ADD `art_type_id` VARCHAR(255);

ALTER TABLE `%TABLE_PREFIX%media` ADD `med_description` TEXT;
ALTER TABLE `%TABLE_PREFIX%media` ADD `med_copyright` TEXT;