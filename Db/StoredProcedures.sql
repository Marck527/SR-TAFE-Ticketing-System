CREATE DEFINER=`root`@`127.0.0.1` PROCEDURE `select_all_users`()
BEGIN
	SELECT * FROM tbl_agent;
END