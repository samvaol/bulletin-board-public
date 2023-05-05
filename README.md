# Simple bulletin board 

just a simple bulletin board...

## SQL Schema

```SQL
CREATE TABLE posts (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  content text,
  created_at datetime,
  media_path varchar(255),
  is_admin tinyint(1) DEFAULT 0,
  nickname varchar(255),
  file_type varchar(10),
  user_ip varchar(45)
);
```
