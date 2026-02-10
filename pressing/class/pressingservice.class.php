<?php

class PressingService
{
    public $db;

    public function __construct(DoliDB $db)
    {
        $this->db = $db;
    }

    public function fetchAll($onlyActive = true)
    {
        global $conf;

        $sql = 'SELECT rowid, ref, label, default_qty, price_ttc, active';
        $sql .= ' FROM '.MAIN_DB_PREFIX.'c_pressing_service';
        $sql .= ' WHERE entity IN ('.getEntity('c_pressing_service').')';
        if ($onlyActive) {
            $sql .= ' AND active = 1';
        }
        $sql .= ' ORDER BY label ASC';

        $resql = $this->db->query($sql);
        $result = array();
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $result[] = $obj;
            }
        }

        return $result;
    }

    public function upsert($id, $ref, $label, $priceTtc, $defaultQty, $active)
    {
        global $conf;

        if ($id > 0) {
            $sql = 'UPDATE '.MAIN_DB_PREFIX.'c_pressing_service';
            $sql .= " SET ref='".$this->db->escape($ref)."',";
            $sql .= " label='".$this->db->escape($label)."',";
            $sql .= ' price_ttc='.price2num($priceTtc).',';
            $sql .= ' default_qty='.price2num($defaultQty).',';
            $sql .= ' active='.(int) $active;
            $sql .= ' WHERE rowid='.(int) $id;
            $sql .= ' AND entity='.(int) $conf->entity;
            return $this->db->query($sql) ? 1 : -1;
        }

        $sql = 'INSERT INTO '.MAIN_DB_PREFIX.'c_pressing_service(entity, ref, label, default_qty, price_ttc, active) VALUES (';
        $sql .= (int) $conf->entity.",'".$this->db->escape($ref)."','".$this->db->escape($label)."',";
        $sql .= price2num($defaultQty).','.price2num($priceTtc).','.(int) $active.')';

        return $this->db->query($sql) ? 1 : -1;
    }

    public function delete($id)
    {
        global $conf;
        $sql = 'DELETE FROM '.MAIN_DB_PREFIX.'c_pressing_service';
        $sql .= ' WHERE rowid='.(int) $id.' AND entity='.(int) $conf->entity;
        return $this->db->query($sql) ? 1 : -1;
    }
}
