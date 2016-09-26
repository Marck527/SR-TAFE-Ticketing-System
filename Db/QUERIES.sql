select * from tbl_agent;

select * from tbl_ticket 
INNER JOIN tbl_agent_ticket
ON 
tbl_ticket.ticket_id = tbl_agent_ticket.ticket_id
INNER JOIN 
tbl_agent ON tbl_agent_ticket.agent_id = tbl_agent.agent_id; 

select * from tbl_agent_ticket;

SELECT 
	ticket_id, submitted_date, ticket_subject, priority_id, status_id
FROM 
	tbl_ticket
WHERE 
	agent_id = 2;




#Select all the tickets who's logged on.

SELECT 
	T.ticket_id, T.submitted_date,  T.ticket_subject, C.category_name,  P.priority_name, S.status_name, A.f_name
FROM
	tbl_ticket T
INNER JOIN
	tbl_priority P
ON 
	T.priority_id = P.priority_id
INNER JOIN
	tbl_status S
ON
	T.status_id = S.status_id
INNER JOIN 
	tbl_category C
ON 
	T.category_id = C.category_id
INNER JOIN
	tbl_agent_ticket AGT
ON 
	T.ticket_id = AGT.ticket_id
INNER JOIN 
	tbl_agent A
ON 
	AGT.agent_id = A.agent_id
WHERE 
	AGT.agent_id = 2
AND 
	S.status_id != 'close';
	
select * from tbl_comment;