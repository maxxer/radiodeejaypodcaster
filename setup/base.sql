CREATE TABLE `programma` (
  `id` INTEGER PRIMARY KEY,
  `slug` varchar(100) UNIQUE NOT NULL,
  `nome` varchar(250) NOT NULL,
  `url_immagine` varchar(255) NOT NULL
) ;


CREATE TABLE `episodio` (
  `id` INTEGER PRIMARY KEY,
  `id_programma` INTEGER,
  `titolo` varchar(250) NOT NULL,
  `href` varchar(255) NOT NULL,
  `url_file` varchar(255) NOT NULL,
  `data_inserimento` datetime NOT NULL,
  FOREIGN KEY(id_programma) REFERENCES programma(id)
) ;
