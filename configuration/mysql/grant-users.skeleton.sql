-- Skeleton file for the user privileges

GRANT USAGE ON *.* TO 'syappread'@'localhost' IDENTIFIED BY 'UUUUUUUUUUUUUUUUUUUUU';
GRANT SELECT ON syncanyapi.app TO 'syappread'@'localhost';

GRANT USAGE ON *.* TO 'syappwrite'@'localhost' IDENTIFIED BY 'VVVVVVVVVVVVVVVVVVVVV';
GRANT SELECT, INSERT, UPDATE ON syncanyapi.app TO 'syappwrite'@'localhost';

GRANT USAGE ON *.* TO 'sypluginsread'@'localhost' IDENTIFIED BY 'WWWWWWWWWWWWWWWWWWWWW';
GRANT SELECT ON syncanyapi.plugins TO 'sypluginsread'@'localhost';

GRANT USAGE ON *.* TO 'sypluginswrite'@'localhost' IDENTIFIED BY 'XXXXXXXXXXXXXXXXXXXXX';
GRANT SELECT, INSERT, UPDATE ON syncanyapi.plugins TO 'sypluginswrite'@'localhost';

GRANT USAGE ON *.* TO 'sylinksread'@'localhost' IDENTIFIED BY 'YYYYYYYYYYYYYYYYYYYYY';
GRANT SELECT ON syncanyapi.links TO 'sylinksread'@'localhost';

GRANT USAGE ON *.* TO 'sylinkswrite'@'localhost' IDENTIFIED BY 'ZZZZZZZZZZZZZZZZZZZZZ';
GRANT SELECT, INSERT ON syncanyapi.links TO 'sylinkswrite'@'localhost';

FLUSH PRIVILEGES;