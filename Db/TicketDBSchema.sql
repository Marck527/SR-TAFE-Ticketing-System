DROP DATABASE IF EXISTS TicketDB;
CREATE DATABASE TicketDB;

USE TicketDB;

CREATE TABLE tbl_priority
(
	priority_id VARCHAR(6) PRIMARY KEY NOT NULL,
    priority_name VARCHAR(20) NOT NULL
);

CREATE TABLE tbl_status
(
	status_id VARCHAR(6) PRIMARY KEY NOT NULL,
    status_name VARCHAR(20) NOT NULL
);

CREATE TABLE tbl_category
(
	category_id VARCHAR(6) PRIMARY KEY NOT NULL,
    category_name VARCHAR(20) NOT NULL
    
);


CREATE TABLE tbl_agent_privilege
(
	agent_privilege_id TINYINT PRIMARY KEY NOT NULL,
	privilege_title VARCHAR(30) NOT NULL
);
CREATE TABLE tbl_agent
(
	agent_id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    f_name VARCHAR(20) NOT NULL,
    l_name VARCHAR(20) NOT NULL,
    email VARCHAR(50) NOT NULL,
    agent_password VARCHAR(100) NOT NULL,
    agent_privilege_id TINYINT NOT NULL,
	
	FOREIGN KEY(agent_privilege_id) REFERENCES tbl_agent_privilege(agent_privilege_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE tbl_comment_type
(
	comment_type_id VARCHAR(6) PRIMARY KEY,
    comment_type_name VARCHAR(20)
    
);

CREATE TABLE tbl_comment
(
	comment_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_comment TEXT,
    comment_datetime DATETIME,
    comment_type_id VARCHAR(6),
    agent_id BIGINT,
    
    FOREIGN KEY(comment_type_id) REFERENCES tbl_comment_type(comment_type_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY(agent_id) REFERENCES tbl_agent(agent_id) ON DELETE RESTRICT ON UPDATE CASCADE
    
);


CREATE TABLE tbl_attachment
(
	attachment_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    attachment_name VARCHAR(100),
    attachment_location VARCHAR(200)
);


CREATE TABLE tbl_attachment_comment
(
	attachment_comment_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    attachment_id BIGINT,
    comment_id BIGINT,
    
    FOREIGN KEY(attachment_id) REFERENCES tbl_attachment(attachment_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY(comment_id) REFERENCES tbl_comment(comment_id) ON DELETE RESTRICT ON UPDATE CASCADE
);


CREATE TABLE tbl_client
(
	client_id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    client_fullname VARCHAR(100),
    client_email VARCHAR(50)
);

CREATE TABLE tbl_ticket
(
	ticket_id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    submitted_date  DATE,
    ticket_subject VARCHAR(100) NOT NULL,
    ticket_body TEXT NOT NULL,
    location VARCHAR(50),
    closed_date DATE,
    client_id BIGINT NOT NULL,
    category_id VARCHAR(6) NOT NULL,
    priority_id VARCHAR(6) NOT NULL,
    status_id VARCHAR(6) NOT NULL,
    assigned_by BIGINT NOT NULL,
    
    FOREIGN KEY(client_id) REFERENCES tbl_client(client_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY(category_id) REFERENCES tbl_category(category_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY(priority_id) REFERENCES tbl_priority(priority_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY(status_id) REFERENCES tbl_status(status_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY(assigned_by) REFERENCES tbl_agent(agent_id) ON DELETE RESTRICT ON UPDATE CASCADE
    
    
);

CREATE TABLE tbl_ticket_attachment
(
	ticket_attachment_id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    ticket_id BIGINT,
    attachment_id BIGINT,
    FOREIGN KEY(ticket_id) REFERENCES tbl_ticket(ticket_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY(attachment_id) REFERENCES tbl_attachment(attachment_id) ON DELETE RESTRICT ON UPDATE CASCADE

);


CREATE TABLE tbl_ticket_comment
(
	ticket_comment_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    comment_id BIGINT,
    ticket_id BIGINT,
    
    FOREIGN KEY(comment_id) REFERENCES tbl_comment(comment_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY(ticket_id) REFERENCES tbl_ticket(ticket_id) ON DELETE RESTRICT ON UPDATE CASCADE
);


CREATE TABLE tbl_agent_ticket
(
	agent_ticket_id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL,
	agent_id BIGINT NOT NULL,
	ticket_id BIGINT NOT NULL,
	
	FOREIGN KEY(agent_id) REFERENCES tbl_agent(agent_id) ON DELETE RESTRICT ON UPDATE CASCADE,
	FOREIGN KEY(ticket_id) REFERENCES tbl_ticket(ticket_id) ON DELETE RESTRICT ON UPDATE CASCADE
);

/* INSERTS PRIVILEGES IN PRIVILEGE TABLE */
INSERT INTO tbl_agent_privilege(agent_privilege_id, privilege_title)VALUES(1, 'Admin');
INSERT INTO tbl_agent_privilege(agent_privilege_id,  privilege_title)VALUES(2, 'Agent');

/* INSERTS PRIORITIES IN PRIORITY TABLE */
INSERT INTO tbl_priority(priority_id, priority_name)VALUES('urg', 'Urgent');
INSERT INTO tbl_priority(priority_id, priority_name)VALUES('high', 'High');
INSERT INTO tbl_priority(priority_id, priority_name)VALUES('med', 'Medium');
INSERT INTO tbl_priority(priority_id, priority_name)VALUES('low', 'Low');


/* INSERTS STATUSES INTO STATUS TABLE */
INSERT INTO tbl_status(status_id, status_name)VALUES('open', 'Open');
INSERT INTO tbl_status(status_id, status_name)VALUES('pend', 'Pending');
INSERT INTO tbl_status(status_id, status_name)VALUES('close', 'Closed');

/* INSERTS CATEGORIES INTO CATEGORY TABLE */
INSERT INTO tbl_category(category_id, category_name)VALUES('soft', 'Software');
INSERT INTO tbl_category(category_id, category_name)VALUES('hard', 'Hardware');
INSERT INTO tbl_category(category_id, category_name)VALUES('netw', 'Network');
INSERT INTO tbl_category(category_id, category_name)VALUES('oth', 'Other');

INSERT INTO tbl_comment_type(comment_type_id, comment_type_name)VALUES('cmnt', 'Comment');
INSERT INTO tbl_comment_type(comment_type_id, comment_type_name)VALUES('sol', 'Solution');

INSERT INTO tbl_client(client_fullname, client_email)VALUES('Bob Demuro', 'Bob@gmail.com');
INSERT INTO tbl_client(client_fullname, client_email)VALUES('Alan Walt', 'Alan@gmail.com');

/* Users */
INSERT INTO tbl_agent(f_name, l_name, email, agent_password, agent_privilege_id)VALUES('Marck', 'Munoz', 'Marck527@gmail.com', '$2y$10$qrk9RjomlU2BhBZcjnWLbuDzU3WctCNJ8pMoJBiMkAI2QhhlpnqCu', 1);
INSERT INTO tbl_agent(f_name, l_name, email, agent_password, agent_privilege_id)VALUES('Rodney', 'Sutton', 'Rodney.Sutton@srtafe.wa.edu.au', '$2y$10$qrk9RjomlU2BhBZcjnWLbuDzU3WctCNJ8pMoJBiMkAI2QhhlpnqCu', 1);
INSERT INTO tbl_agent(f_name, l_name, email, agent_password, agent_privilege_id)VALUES('Admin', '123', 'Admin@srtafe.wa.edu.au', '$2y$10$qrk9RjomlU2BhBZcjnWLbuDzU3WctCNJ8pMoJBiMkAI2QhhlpnqCu', 1);



