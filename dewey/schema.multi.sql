DROP DATABASE projbd;
CREATE DATABASE projbd CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;
USE projbd;
CREATE TABLE docs (
    doc_id integer,
    document varchar(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

CREATE TABLE path (
	doc_id integer,
    path_id integer,
    path varchar(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

CREATE TABLE edge (
    doc_id integer,
    path_id integer,
    d1 int,
    d2 int,
    d3 int,
    d4 int,
    d5 int,
    d6 int,
    d7 int,
    value text
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci;

ALTER TABLE docs ADD PRIMARY KEY (doc_id);
ALTER TABLE path ADD PRIMARY KEY (doc_id,path_id);
ALTER TABLE path ADD FOREIGN KEY (doc_id) REFERENCES docs (doc_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE edge ADD PRIMARY KEY (doc_id,d1,d2,d3,d4,d5,d6,d7);
ALTER TABLE edge ADD KEY path_id (path_id);
ALTER TABLE edge ADD FOREIGN KEY (doc_id) REFERENCES docs (doc_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE edge ADD FOREIGN KEY (doc_id,path_id) REFERENCES path (doc_id,path_id) ON UPDATE CASCADE;
