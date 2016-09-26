USE TicketDB;


INSERT INTO
	tbl_attachment(attachment_location)
	VALUES('Uploads/test1.txt');
                                      
SET @ATT_ID = LAST_INSERT_ID();
                                    
INSERT INTO
	tbl_attachment_comment(attachment_id, comment_id)
	VALUES(@ATT_ID, 1);


	
SELECT * FROM tbl_comment;
select * from tbl_attachment A INNER JOIN tbl_attachment_comment AC ON A.attachment_id=AC.attachment_id INNER JOIN tbl_comment C ON AC.comment_id = C.comment_id;

SELECT 
	      *
        FROM
	      tbl_ticket T
        LEFT JOIN
	      tbl_ticket_comment TC
        ON
	      T.ticket_id = TC.ticket_id
        LEFT JOIN
	      tbl_comment C
        ON
	      TC.comment_id = C.comment_id
        LEFT  JOIN
	      tbl_comment_type CT
        ON
	      C.comment_type_id = CT.comment_type_id
        LEFT  JOIN
	      tbl_attachment_comment AC
        ON
	      C.comment_id = AC.comment_id
        LEFT  JOIN
	      tbl_attachment ATT
        ON
	      AC.attachment_id = ATT.attachment_id
	    LEFT JOIN
	      tbl_agent A
	    ON
	      C.agent_id = A.agent_id
	    WHERE
	      T.ticket_id = 1
	    GROUP BY
	      C.comment_id
	    ORDER BY
	      CT.comment_type_id DESC, C.comment_datetime DESC;
          
          
          select * from tbl_attachment_comment;
          
          
          
          
		SELECT
			*
		FROM
			tbl_comment C
		LEFT JOIN
			tbl_attachment_comment AC
		ON
			C.comment_id = AC.comment_id
		LEFT JOIN
			tbl_attachment A
		ON
			AC.attachment_id = A.attachment_id;
            
            
		
        