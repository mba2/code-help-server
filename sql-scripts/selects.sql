USE SOCCER_GUESS;
-- USE u989271099_sg;
-- ALL TOURNAMENTS
SELECT * FROM SG_TOURNAMENTS;
SELECT * FROM SG_GROUPS;
 
 
-- SELECT * FROM SG_GROUP_FORMATIONS;
-- 
 -- ALL TEAMS
-- SELECT * FROM SG_TEAMS;
-- -- ALL GROUPS
-- SELECT 
-- 	G.`ID`,
-- 	G.`NAME`,
-- 	T.`NAME` AS `BELONGS TO`
-- 	 FROM `SG_GROUPS` AS G
-- 	 INNER JOIN `SG_TOURNAMENTS` AS T 
--      ON G.`TOURNAMENT_ID` = T.`ID`
-- 	 WHERE G.`ACTIVE` != 0
--      ORDER BY(G.`ID`);
-- 
-- SELECT 
-- 	`TOR`.`ID` AS 'tournamentId',
-- 	`TOR`.`NAME` AS 'tournament',
--     `G`.`ID` AS 'groupId',
-- 	`G`.`NAME` AS 'group',
-- 	`TE`.`FULLNAME` AS 'team',
--     `TE`.`SHORTNAME` AS 'shortname',
--     `TE`.`FLAG` AS 'flag'
--     FROM `SG_GROUP_FORMATIONS`AS `F`
-- 		  INNER JOIN `SG_GROUPS` AS `G` ON(`G`.`ID` = `F`.`GROUP_ID`)
--           INNER JOIN `SG_TOURNAMENTS` AS `TOR` ON(`G`.`TOURNAMENT_ID` = `TOR`.`ID`)
-- 	      INNER JOIN `SG_TEAMS` AS `TE` ON(`TE`.`ID` = `F`.`TEAM_ID`);
--      
-- -- ALL GROUPS OF A TOURNAMENT
-- SELECT 
-- 	`TOR`.`NAME` AS 'TOURNAMENT',
-- 	`G`.`NAME` AS 'GROUP',
-- 	`TE`.`FULLNAME` AS 'TEAM'
--     FROM `SG_GROUP_FORMATIONS`AS `F`
-- 		  INNER JOIN `SG_GROUPS` AS `G` ON(`G`.`ID` = `F`.`GROUP_ID`)
--           INNER JOIN `SG_TOURNAMENTS` AS `TOR` ON(`G`.`TOURNAMENT_ID` = `TOR`.`ID`)
-- 	      INNER JOIN `SG_TEAMS` AS `TE` ON(`TE`.`ID` = `F`.`TEAM_ID`)
-- 			WHERE `G`.`TOURNAMENT_ID` = 2;
--                     
-- 		
-- -- ALL GROUPS OF A RANGE OF TOURNAMENTS WITH THEIR TEAMS
-- SELECT 
-- 	`TOR`.`NAME` AS 'TOURNAMENT',
-- 	`G`.`NAME` AS 'GROUP',
-- 	`TE`.`FULLNAME` AS 'TEAM'
--     FROM `SG_GROUP_FORMATIONS`AS `F`
-- 		  INNER JOIN `SG_GROUPS` AS `G` ON(`G`.`ID` = `F`.`GROUP_ID`)
--           INNER JOIN `SG_TOURNAMENTS` AS `TOR` ON(`G`.`TOURNAMENT_ID` = `TOR`.`ID`)
-- 	      INNER JOIN `SG_TEAMS` AS `TE` ON(`TE`.`ID` = `F`.`TEAM_ID`)
-- 			WHERE `G`.`TOURNAMENT_ID` IN(1,2);
