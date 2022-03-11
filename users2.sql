use comp440;

CREATE TABLE users (
    username varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    password varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    first_name varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,   
    last_name varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
	email VARCHAR(100) NOT NULL UNIQUE KEY,
	id INT NOT NULL AUTO_INCREMENT,
    Unique KEY (id),
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	count INT DEFAULT 0,
	email_verification_link VARCHAR(255) NOT NULL,
	email_verified_at TIMESTAMP,                       
	dob DATE,                                     
	ans VARCHAR(255),                 
	ques INT,                         
	tfaen INT,                         
	tfa VARCHAR(255),
	PRIMARY KEY (username)         
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS all_login_attempts (
	username VARCHAR(50) NOT NULL,
	password VARCHAR(255) NOT NULL,
	attempt_date DATETIME,
	ip VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS login_attempts LIKE all_login_attempts;

CREATE TABLE IF NOT EXISTS password_reset_temp (
	email VARCHAR(250),
	keyTO VARCHAR(255),
	expD DATETIME
);

DROP TABLE IF EXISTS blog_created_by;
DROP TABLE IF EXISTS blogs;

CREATE TABLE blogs (
  blogid int(10) unsigned NOT NULL AUTO_INCREMENT,
  subject varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  description varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  pdate date DEFAULT NULL,
  created_by varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (blogid),
  KEY FK1_idx (description),
  KEY FK1 (created_by),
  CONSTRAINT FK1 FOREIGN KEY (created_by) REFERENCES users (username)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS blogstags;

CREATE TABLE blogstags (
  blogid int(10) unsigned NOT NULL,
  tag varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (blogid,tag),
  CONSTRAINT blogstags_ibfk_1 FOREIGN KEY (blogid) REFERENCES blogs (blogid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS comments;

CREATE TABLE comments (
  commentid int(10) NOT NULL AUTO_INCREMENT,
  sentiment varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  description varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  cdate date DEFAULT NULL,
  blogid int(10) unsigned DEFAULT NULL,
  posted_by varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (commentid),
  KEY comments_ibfk_1 (blogid),
  KEY comments_ibfk_2 (posted_by),
  CONSTRAINT comments_ibfk_1 FOREIGN KEY (blogid) REFERENCES blogs (blogid),
  CONSTRAINT comments_ibfk_2 FOREIGN KEY (posted_by) REFERENCES users (username),
  CONSTRAINT sentiment_types CHECK ((sentiment in (_utf8mb4'negative',_utf8mb4'positive')))
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS follows;

CREATE TABLE follows (
  leadername varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  followername varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (leadername,followername),
  KEY follows_ibfk_2 (followername),
  CONSTRAINT follows_ibfk_1 FOREIGN KEY (leadername) REFERENCES users (username),
  CONSTRAINT follows_ibfk_2 FOREIGN KEY (followername) REFERENCES users (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS hobbies;

CREATE TABLE hobbies (
  username varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  hobby varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (hobby,username),
  KEY hobbies_ibfk_1 (username),
  CONSTRAINT hobbies_ibfk_1 FOREIGN KEY (username) REFERENCES users (username),
  CONSTRAINT hobby_types CHECK ((hobby in (_utf8mb4'hiking',_utf8mb4'swimming',_utf8mb4'calligraphy',_utf8mb4'bowling',_utf8mb4'movie',_utf8mb4'cooking',_utf8mb4'dancing')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;