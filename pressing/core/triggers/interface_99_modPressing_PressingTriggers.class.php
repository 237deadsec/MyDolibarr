<?php

class InterfacePressingTriggers
{
    public $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
    {
        if (empty($conf->pressing->enabled)) {
            return 0;
        }

        if ($action === 'BILL_PAYED') {
            $sql = 'UPDATE '.MAIN_DB_PREFIX.'pressing_order';
            $sql .= ' SET status = CASE WHEN fk_facture_final = '.((int) $object->id).' THEN 3 ELSE status END,';
            $sql .= " date_delivery = CASE WHEN fk_facture_final = ".((int) $object->id)." THEN '".$this->db->idate(dol_now())."' ELSE date_delivery END";
            $sql .= ' WHERE fk_facture_final = '.((int) $object->id).' OR fk_facture_deposit = '.((int) $object->id);
            $this->db->query($sql);
        }

        return 0;
    }
}
