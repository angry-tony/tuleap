CREATE TABLE IF NOT EXISTS plugin_gitlab_repository (
    id INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    gitlab_id INT(11) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    full_url VARCHAR(255) NOT NULL,
    last_push_date INT(11) NOT NULL,
    UNIQUE (gitlab_id, full_url)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS plugin_gitlab_repository_project (
    id INT(11) NOT NULL,
    project_id INT(11) NOT NULL,
    PRIMARY KEY (id, project_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS plugin_gitlab_repository_webhook_secret (
    id INT(11) NOT NULL PRIMARY KEY,
    webhook_secret BLOB NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS plugin_gitlab_commit_info (
    id INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    gitlab_repository_id INT(11) NOT NULL,
    commit_sha1 BINARY(20) NOT NULL,
    commit_date INT(11) NOT NULL,
    commit_title TEXT NOT NULL,
    author_name TEXT NOT NULL,
    author_email TEXT NOT NULL
) ENGINE=InnoDB;
