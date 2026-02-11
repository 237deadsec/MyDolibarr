CREATE TABLE llx_c_pressing_service (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  entity integer NOT NULL DEFAULT 1,
  ref varchar(30) NOT NULL,
  label varchar(255) NOT NULL,
  default_qty double(24,8) NOT NULL DEFAULT 1,
  price_ttc double(24,8) NOT NULL,
  active tinyint NOT NULL DEFAULT 1
) ENGINE=innodb;

ALTER TABLE llx_c_pressing_service ADD UNIQUE INDEX uk_c_pressing_service_ref_entity (entity, ref);

INSERT INTO llx_c_pressing_service(entity, ref, label, default_qty, price_ttc, active) VALUES
(1, 'CHEMISE', 'Nettoyage chemise', 1, 4.50, 1),
(1, 'PANTALON', 'Nettoyage pantalon', 1, 6.90, 1),
(1, 'VESTE', 'Nettoyage veste', 1, 8.90, 1),
(1, 'ROBE', 'Nettoyage robe', 1, 12.00, 1),
(1, 'COUETTE', 'Nettoyage couette', 1, 24.90, 1);
