USE hostel;

CREATE TABLE IF NOT EXISTS notifications (
  id INT(11) NOT NULL AUTO_INCREMENT,
  receiver_email VARCHAR(255) NOT NULL COMMENT 'Email of the user or "all" for broadcast',
  title VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
