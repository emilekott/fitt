CREATE  TABLE IF NOT EXISTS `#__rokgallery_files` (
  `id` INT UNSIGNED NULL DEFAULT NULL AUTO_INCREMENT ,
  `filename` VARCHAR(255) NOT NULL ,
  `guid` CHAR(36) NOT NULL ,
  `md5` CHAR(32) NOT NULL ,
  `title` VARCHAR(200) NOT NULL ,
  `description` TEXT NULL DEFAULT NULL ,
  `license` VARCHAR(255) NULL DEFAULT NULL ,
  `xsize` INT UNSIGNED NOT NULL ,
  `ysize` INT UNSIGNED NOT NULL ,
  `filesize` INT UNSIGNED NOT NULL ,
  `type` CHAR(20) NOT NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '0' ,
  `created_at` DATETIME NOT NULL ,
  `updated_at` DATETIME NOT NULL ,
  `slug` VARCHAR(255) NULL DEFAULT NULL ,
  UNIQUE INDEX (`id` ASC) ,
  UNIQUE INDEX (`guid` ASC) ,
  INDEX `#__rokgallery_files_published_idx` (`published` ASC) ,
  INDEX `#__rokgallery_files_md5_idx` (`md5` ASC) ,
  INDEX `#__rokgallery_files_guid_idx` (`guid` ASC) ,
  UNIQUE INDEX `#__files_sluggable_idx` (`slug` ASC) ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;

CREATE  TABLE IF NOT EXISTS `#__rokgallery_files_index` (
  `keyword` VARCHAR(200) NULL DEFAULT NULL ,
  `field` VARCHAR(50) NULL DEFAULT NULL ,
  `position` BIGINT NULL DEFAULT NULL ,
  `id` INT UNSIGNED NULL DEFAULT NULL ,
  INDEX `#__rokgallery_files_index_id_idx` (`id` ASC) ,
  PRIMARY KEY (`keyword`, `field`, `position`, `id`) ,
  CONSTRAINT `#__rokgallery_files_index_id_idx`
    FOREIGN KEY (`id` )
    REFERENCES `#__rokgallery_files` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

CREATE  TABLE IF NOT EXISTS `#__rokgallery_galleries` (
  `id` INT UNSIGNED NULL DEFAULT NULL AUTO_INCREMENT ,
  `name` VARCHAR(200) NOT NULL ,
  `filetags` LONGTEXT NULL DEFAULT NULL ,
  `width` INT UNSIGNED NOT NULL DEFAULT 910 ,
  `height` INT UNSIGNED NOT NULL DEFAULT 500 ,
  `keep_aspect` TINYINT(1) NULL DEFAULT '0' ,
  `force_image_size` TINYINT(1) NULL DEFAULT '0' ,
  `thumb_xsize` INT UNSIGNED NOT NULL DEFAULT 190 ,
  `thumb_ysize` INT UNSIGNED NOT NULL DEFAULT 150 ,
  `thumb_background` VARCHAR(12) NULL DEFAULT NULL ,
  `thumb_keep_aspect` TINYINT(1) NULL DEFAULT '0' ,
  `auto_publish` TINYINT(1) NULL DEFAULT '0' ,
  UNIQUE INDEX (`id` ASC) ,
  INDEX `#__rokgallery_galleries_auto_publish_idx` (`auto_publish` ASC) ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;

CREATE  TABLE IF NOT EXISTS `#__rokgallery_slices` (
  `id` INT UNSIGNED NULL DEFAULT NULL AUTO_INCREMENT ,
  `file_id` INT UNSIGNED NOT NULL ,
  `gallery_id` INT UNSIGNED NULL DEFAULT NULL ,
  `guid` CHAR(36) NOT NULL ,
  `title` VARCHAR(200) NULL DEFAULT NULL ,
  `caption` TEXT NULL DEFAULT NULL ,
  `link` TEXT NULL DEFAULT NULL ,
  `filesize` INT UNSIGNED NOT NULL ,
  `xsize` INT UNSIGNED NOT NULL ,
  `ysize` INT UNSIGNED NOT NULL ,
  `published` TINYINT(1) NOT NULL DEFAULT '0' ,
  `admin_thumb` TINYINT(1) NOT NULL DEFAULT '0' ,
  `manipulations` LONGTEXT NULL DEFAULT NULL ,
  `palette` TEXT NULL DEFAULT NULL ,
  `created_at` DATETIME NOT NULL ,
  `updated_at` DATETIME NOT NULL ,
  `slug` VARCHAR(255) NULL DEFAULT NULL ,
  `thumb_xsize` int(10) unsigned NOT NULL,
  `thumb_ysize` int(10) unsigned NOT NULL,
  `thumb_keep_aspect` tinyint(1) NOT NULL DEFAULT '1',
  `thumb_background` varchar(12) DEFAULT NULL,
  `ordering` int(10) unsigned NOT NULL,
  UNIQUE INDEX (`id` ASC) ,
  UNIQUE INDEX (`guid` ASC) ,
  INDEX `rokgallery_slices_published_idx` (`published` ASC) ,
  INDEX `rokgallery_slices_guid_idx` (`guid` ASC) ,
  UNIQUE INDEX `#__rokgallery_slices_sluggable_idx` (`slug` ASC, `gallery_id` ASC) ,
  INDEX `file_id_idx` (`file_id` ASC) ,
  INDEX `gallery_id_idx` (`gallery_id` ASC) ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `#__slices_gallery_id_galleries_id`
    FOREIGN KEY (`gallery_id` )
    REFERENCES `#__rokgallery_galleries` (`id` )
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `#__slices_file_id_files_id`
    FOREIGN KEY (`file_id` )
    REFERENCES `#__rokgallery_files` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

CREATE  TABLE IF NOT EXISTS `#__rokgallery_slices_index` (
  `keyword` VARCHAR(200) NULL DEFAULT NULL ,
  `field` VARCHAR(50) NULL DEFAULT NULL ,
  `position` BIGINT NULL DEFAULT NULL ,
  `id` INT UNSIGNED NULL DEFAULT NULL ,
  INDEX `rokgallery_slices_index_id_idx` (`id` ASC) ,
  PRIMARY KEY (`keyword`, `field`, `position`, `id`) ,
  CONSTRAINT `#__rokgallery_slices_index_id_idx`
    FOREIGN KEY (`id` )
    REFERENCES `#__rokgallery_slices` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

CREATE  TABLE IF NOT EXISTS `#__rokgallery_file_loves` (
  `file_id` INT UNSIGNED NULL DEFAULT NULL ,
  `kount` INT NOT NULL DEFAULT 0 ,
  UNIQUE INDEX (`file_id` ASC) ,
  PRIMARY KEY (`file_id`) ,
  CONSTRAINT `#__file_loves_file_id_files_id`
    FOREIGN KEY (`file_id` )
    REFERENCES `#__rokgallery_files` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


CREATE  TABLE IF NOT EXISTS `#__rokgallery_file_tags` (
  `file_id` INT UNSIGNED NULL DEFAULT NULL ,
  `tag` VARCHAR(50) NULL DEFAULT NULL ,
  INDEX `#__rokgallery_file_tags_file_id_idx` (`file_id` ASC) ,
  PRIMARY KEY (`file_id`, `tag`) ,
  CONSTRAINT `#__file_tags_file_id_files_id`
    FOREIGN KEY (`file_id` )
    REFERENCES `#__rokgallery_files` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


CREATE  TABLE IF NOT EXISTS `#__rokgallery_file_views` (
  `file_id` INT UNSIGNED NULL DEFAULT NULL ,
  `kount` INT NOT NULL DEFAULT 0 ,
  UNIQUE INDEX (`file_id` ASC) ,
  PRIMARY KEY (`file_id`) ,
  CONSTRAINT `#__file_views_file_id__files_id`
    FOREIGN KEY (`file_id` )
    REFERENCES `#__rokgallery_files` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


CREATE  TABLE IF NOT EXISTS `#__rokgallery_filters` (
  `id` INT UNSIGNED NULL DEFAULT NULL AUTO_INCREMENT ,
  `name` VARCHAR(200) NOT NULL ,
  `query` TEXT NOT NULL ,
  `created_at` DATETIME NOT NULL ,
  `updated_at` DATETIME NOT NULL ,
  UNIQUE INDEX (`id` ASC) ,
  INDEX `rokgallery_profiles_name_idx` (`name` ASC) ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


CREATE  TABLE IF NOT EXISTS `#__rokgallery_jobs` (
  `id` CHAR(36) NULL DEFAULT NULL ,
  `type` VARCHAR(255) NOT NULL ,
  `properties` TEXT NULL DEFAULT NULL ,
  `state` VARCHAR(255) NOT NULL ,
  `status` TEXT NULL DEFAULT NULL ,
  `percent` BIGINT UNSIGNED NULL DEFAULT NULL ,
  `sm` TEXT NOT NULL ,
  `created_at` DATETIME NOT NULL ,
  `updated_at` DATETIME NOT NULL ,
  UNIQUE INDEX (`id` ASC) ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


CREATE  TABLE IF NOT EXISTS `#__rokgallery_profiles` (
  `id` INT UNSIGNED NULL DEFAULT NULL AUTO_INCREMENT ,
  `name` VARCHAR(200) NOT NULL ,
  `profile` TEXT NOT NULL ,
  `created_at` DATETIME NOT NULL ,
  `updated_at` DATETIME NOT NULL ,
  UNIQUE INDEX (`id` ASC) ,
  INDEX `#__rokgallery_profiles_name_idx` (`name` ASC) ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


CREATE  TABLE IF NOT EXISTS `#__rokgallery_slice_tags` (
  `slice_id` INT UNSIGNED NULL DEFAULT NULL ,
  `tag` VARCHAR(50) NULL DEFAULT NULL ,
  INDEX `rokgallery_slice_tags_slice_id_idx` (`slice_id` ASC) ,
  PRIMARY KEY (`slice_id`, `tag`) ,
  CONSTRAINT `#__slice_tags_slice_id_slices_id`
    FOREIGN KEY (`slice_id` )
    REFERENCES `#__rokgallery_slices` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `#__rokgallery_schema_version` (
  `version` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `#__rokgallery_schema_version` (`version`)
VALUES (1);