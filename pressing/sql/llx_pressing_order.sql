CREATE TABLE llx_pressing_order (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  ref varchar(64) NOT NULL,
  entity integer NOT NULL DEFAULT 1,
  fk_soc integer NOT NULL,
  fk_user_author integer NOT NULL,
  status smallint NOT NULL DEFAULT 0,
  note_private text,
  date_reception datetime NOT NULL,
  date_due datetime NOT NULL,
  date_ready datetime DEFAULT NULL,
  date_delivery datetime DEFAULT NULL,
  total_ttc double(24,8) NOT NULL DEFAULT 0,
  deposit_ttc double(24,8) NOT NULL DEFAULT 0,
  fk_facture_deposit integer DEFAULT NULL,
  fk_facture_final integer DEFAULT NULL,
  tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=innodb;

ALTER TABLE llx_pressing_order ADD UNIQUE INDEX uk_pressing_order_ref (ref);
ALTER TABLE llx_pressing_order ADD INDEX idx_pressing_order_soc (fk_soc);
ALTER TABLE llx_pressing_order ADD INDEX idx_pressing_order_deposit_inv (fk_facture_deposit);
ALTER TABLE llx_pressing_order ADD INDEX idx_pressing_order_final_inv (fk_facture_final);

CREATE TABLE llx_pressing_orderdet (
  rowid integer AUTO_INCREMENT PRIMARY KEY,
  fk_pressing_order integer NOT NULL,
  fk_service integer DEFAULT NULL,
  description varchar(255) NOT NULL,
  qty double(24,8) NOT NULL DEFAULT 1,
  unit_price_ttc double(24,8) NOT NULL DEFAULT 0,
  total_ttc double(24,8) NOT NULL DEFAULT 0
) ENGINE=innodb;

ALTER TABLE llx_pressing_orderdet ADD INDEX idx_pressing_orderdet_order (fk_pressing_order);
