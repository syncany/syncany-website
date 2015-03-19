CREATE TABLE IF NOT EXISTS `app` (
  `id` int(14) NOT NULL AUTO_INCREMENT,
  `dist` enum('cli','gui') NOT NULL,
  `type` varchar(255) NOT NULL,
  `appVersion` varchar(255) NOT NULL,
  `os` varchar(255) NOT NULL DEFAULT 'all',
  `arch` varchar(255) NOT NULL DEFAULT 'all',
  `date` varchar(255) NOT NULL,
  `release` int(1) NOT NULL DEFAULT '0',
  `checksum` varchar(255) NOT NULL,
  `basename` varchar(255) NOT NULL,
  `fullpath` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `appVersion` (`appVersion`),
  KEY `os` (`os`),
  KEY `arch` (`arch`),
  KEY `release` (`release`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `plugins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pluginId` varchar(255) NOT NULL,
  `pluginName` varchar(255) NOT NULL,
  `pluginVersion` varchar(255) NOT NULL,
  `pluginOperatingSystem` varchar(255) NOT NULL DEFAULT 'all',
  `pluginArchitecture` varchar(255) NOT NULL DEFAULT 'all',
  `pluginDate` varchar(255) NOT NULL,
  `pluginAppMinVersion` varchar(255) NOT NULL,
  `pluginRelease` int(1) NOT NULL DEFAULT '0',
  `pluginConflictsWith` varchar(255) DEFAULT NULL,
  `sha256sum` varchar(255) NOT NULL,
  `filenameBasename` varchar(255) NOT NULL,
  `filenameFull` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pluginId` (`pluginId`),
  KEY `pluginDate` (`pluginDate`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `links` (
  `id` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  `longlink` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
