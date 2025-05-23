
tievi22_ostosesimerkki_login

CREATE TABLE kayttajat (
	id INT AUTO_INCREMENT PRIMARY KEY,
	nimi VARCHAR(50) NOT NULL UNIQUE,
	salasana VARCHAR(255) NOT NULL,
	rooli VARCHAR(100) NOT NULL DEFAULT 'perus'
);

CREATE TABLE IF NOT EXISTS ostos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hinta DECIMAL(14,2) DEFAULT NULL,
  nimi VARCHAR(150) DEFAULT NULL,
  kayttaja INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (kayttaja) REFERENCES kayttajat(id) ON DELETE CASCADE
);



//VOISI muokata edellisen version ostos taulusta...
ALTER TABLE ostos
ADD kayttaja INT NOT NULL AFTER nimi,
ADD CONSTRAINT fk_kayttaja FOREIGN KEY (kayttaja) REFERENCES kayttajat(id) ON DELETE CASCADE;














