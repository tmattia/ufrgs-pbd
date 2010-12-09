DROP DATABASE shaks;
CREATE DATABASE shaks;
USE shaks;

CREATE TABLE docs_global (
    doc_id int(11) NOT NULL AUTO_INCREMENT,
    document varchar(50) NOT NULL,
    PRIMARY KEY (doc_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE path_global (
    doc_id int(11) NOT NULL,
    path_id int(11) NOT NULL,
    path varchar(100) NOT NULL,
    PRIMARY KEY (doc_id, path_id),
    FOREIGN KEY (doc_id) REFERENCES docs_global (doc_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE edge_global (
    doc_id int(11) NOT NULL,
    id int(11) NOT NULL,
    parent_id int(11) NOT NULL,
    end_desc_id int(11) NOT NULL,
    path_id int(11) NOT NULL,
    value text,
    PRIMARY KEY (doc_id, id),
    KEY path_id (path_id),
    KEY parent_id (parent_id),
    KEY id (id),
    FOREIGN KEY (doc_id, path_id) REFERENCES path_global (doc_id, path_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;