USE TicketDB;

SHOW TABLES;
SELECT * FROM tbl_attachment;
SELECT * FROM tbl_ticket_attachment;

SELECT * FROM tbl_ticket T 
LEFT JOIN tbl_ticket_attachment TA 
ON T.ticket_id = TA.ticket_id 
LEFT JOIN tbl_attachment A 
ON TA.attachment_id = A.attachment_id;



SET @ATT_ID = LAST_INSERT_ID();

INSERT INTO
tbl_ticket_attachment(ticket_id, attachment_id)
VALUES(3, @ATT_ID);

 INSERT INTO
          tbl_attachment(attachment_name, attachment_location)
            VALUES('57ca691591aa32.38880660.jpg', 'uploads/57ca691591aa32.38880660.jpg');
            
SELECT * FROM tbl_comment CMNT
LEFT JOIN
	tbl_attachment_comment ATC
ON CMNT.comment_id = ATC.comment_id
LEFT JOIN
	tbl_attachment ATT
ON
ATC.attachment_id = ATT.attachment_id;










 INSERT INTO
              tbl_attachment(attachment_name, attachment_location)
                VALUES('were', 'destination');
                                      
            SET @ATT_ID = LAST_INSERT_ID();
                                    
            INSERT INTO
              tbl_attachment_comment(attachment_id, comment_id)
                VALUES(@ATT_ID, 3);




	