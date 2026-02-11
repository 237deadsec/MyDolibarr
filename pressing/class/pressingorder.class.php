<?php
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';

class PressingOrder extends CommonObject
{
    public $element = 'pressing_order';
    public $table_element = 'pressing_order';
    public $ismultientitymanaged = 1;

    public $id;
    public $ref;
    public $entity;
    public $fk_soc;
    public $fk_user_author;
    public $status;
    public $note_private;
    public $date_reception;
    public $date_due;
    public $date_ready;
    public $date_delivery;
    public $total_ttc;
    public $deposit_ttc;
    public $fk_facture_deposit;
    public $fk_facture_final;
    public $lines = array();

    public function __construct(DoliDB $db)
    {
        $this->db = $db;
    }

    public function create(User $user)
    {
        $this->db->begin();

        $this->ref = $this->getNextNumRef();
        $sql = 'INSERT INTO '.MAIN_DB_PREFIX.$this->table_element;
        $sql .= '(ref, entity, fk_soc, fk_user_author, status, note_private, date_reception, date_due, total_ttc, deposit_ttc) VALUES (';
        $sql .= "'".$this->db->escape($this->ref)."',";
        $sql .= (int) $this->entity.',';
        $sql .= (int) $this->fk_soc.',';
        $sql .= (int) $user->id.',';
        $sql .= (int) $this->status.",'".$this->db->escape($this->note_private)."',";
        $sql .= "'".$this->db->idate($this->date_reception)."','".$this->db->idate($this->date_due)."',";
        $sql .= price2num($this->total_ttc).','.price2num($this->deposit_ttc).')';

        if (!$this->db->query($sql)) {
            $this->error = $this->db->lasterror();
            $this->db->rollback();
            return -1;
        }

        $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX.$this->table_element);
        if ($this->insertLines() < 0) {
            $this->db->rollback();
            return -1;
        }

        $this->db->commit();
        return $this->id;
    }

    public function insertLines()
    {
        foreach ($this->lines as $line) {
            $qty = price2num($line['qty']);
            $unit = price2num($line['unit_price_ttc']);
            $total = price2num($qty * $unit);
            $sql = 'INSERT INTO '.MAIN_DB_PREFIX.'pressing_orderdet';
            $sql .= '(fk_pressing_order, fk_service, description, qty, unit_price_ttc, total_ttc) VALUES (';
            $sql .= (int) $this->id.','.(int) $line['fk_service'].",'".$this->db->escape($line['description'])."',";
            $sql .= $qty.','.$unit.','.$total.')';
            if (!$this->db->query($sql)) {
                $this->error = $this->db->lasterror();
                return -1;
            }
        }
        return 1;
    }

    public function fetch($id)
    {
        $sql = 'SELECT rowid, ref, entity, fk_soc, fk_user_author, status, note_private, date_reception, date_due, date_ready, date_delivery, total_ttc, deposit_ttc, fk_facture_deposit, fk_facture_final';
        $sql .= ' FROM '.MAIN_DB_PREFIX.$this->table_element;
        $sql .= ' WHERE rowid='.(int) $id;
        $resql = $this->db->query($sql);
        if (!$resql) {
            $this->error = $this->db->lasterror();
            return -1;
        }
        if ($this->db->num_rows($resql) <= 0) {
            return 0;
        }

        $obj = $this->db->fetch_object($resql);
        foreach (array('rowid' => 'id', 'ref' => 'ref', 'entity' => 'entity', 'fk_soc' => 'fk_soc', 'fk_user_author' => 'fk_user_author', 'status' => 'status', 'note_private' => 'note_private', 'total_ttc' => 'total_ttc', 'deposit_ttc' => 'deposit_ttc', 'fk_facture_deposit' => 'fk_facture_deposit', 'fk_facture_final' => 'fk_facture_final') as $k => $prop) {
            $this->{$prop} = $obj->{$k};
        }
        $this->date_reception = $this->db->jdate($obj->date_reception);
        $this->date_due = $this->db->jdate($obj->date_due);
        $this->date_ready = $this->db->jdate($obj->date_ready);
        $this->date_delivery = $this->db->jdate($obj->date_delivery);

        $this->fetchLines();
        return 1;
    }

    public function fetchLines()
    {
        $this->lines = array();
        $sql = 'SELECT rowid, fk_service, description, qty, unit_price_ttc, total_ttc';
        $sql .= ' FROM '.MAIN_DB_PREFIX.'pressing_orderdet';
        $sql .= ' WHERE fk_pressing_order='.(int) $this->id;
        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $this->lines[] = array(
                    'id' => $obj->rowid,
                    'fk_service' => $obj->fk_service,
                    'description' => $obj->description,
                    'qty' => $obj->qty,
                    'unit_price_ttc' => $obj->unit_price_ttc,
                    'total_ttc' => $obj->total_ttc,
                );
            }
        }
    }

    public function markReady()
    {
        $sql = 'UPDATE '.MAIN_DB_PREFIX.$this->table_element;
        $sql .= " SET status=2, date_ready='".$this->db->idate(dol_now())."'";
        $sql .= ' WHERE rowid='.(int) $this->id;
        return $this->db->query($sql) ? 1 : -1;
    }

    public function createDepositInvoice(User $user, $amountTtc)
    {
        $amountTtc = price2num($amountTtc);
        if ($amountTtc <= 0 || $amountTtc > $this->total_ttc) {
            $this->error = 'Invalid deposit amount';
            return -1;
        }

        $invoiceId = $this->createInvoiceForAmount($user, $amountTtc, 'Acompte pressing - commande '.$this->ref, true);
        if ($invoiceId <= 0) {
            return -1;
        }

        $sql = 'UPDATE '.MAIN_DB_PREFIX.$this->table_element;
        $sql .= ' SET deposit_ttc='.$amountTtc.', fk_facture_deposit='.(int) $invoiceId;
        $sql .= ' WHERE rowid='.(int) $this->id;
        if (!$this->db->query($sql)) {
            $this->error = $this->db->lasterror();
            return -1;
        }

        $this->deposit_ttc = $amountTtc;
        $this->fk_facture_deposit = $invoiceId;
        return $invoiceId;
    }

    public function createFinalInvoice(User $user)
    {
        $remaining = price2num($this->total_ttc - $this->deposit_ttc);
        if ($remaining < 0) {
            $remaining = 0;
        }
        $invoiceId = $this->createInvoiceForAmount($user, $remaining, 'Solde pressing - commande '.$this->ref, false);
        if ($invoiceId <= 0) {
            return -1;
        }

        $sql = 'UPDATE '.MAIN_DB_PREFIX.$this->table_element;
        $sql .= " SET fk_facture_final=".(int) $invoiceId.", status=3, date_delivery='".$this->db->idate(dol_now())."'";
        $sql .= ' WHERE rowid='.(int) $this->id;
        if (!$this->db->query($sql)) {
            $this->error = $this->db->lasterror();
            return -1;
        }

        $this->fk_facture_final = $invoiceId;
        $this->status = 3;
        return $invoiceId;
    }

    private function createInvoiceForAmount(User $user, $amountTtc, $label, $isDeposit)
    {
        global $conf;

        $invoice = new Facture($this->db);
        $invoice->socid = $this->fk_soc;
        $invoice->date = dol_now();
        $invoice->type = 0;
        $invoice->note_private = $label;

        $result = $invoice->create($user);
        if ($result <= 0) {
            $this->error = $invoice->error;
            return -1;
        }

        $vat = (float) getDolGlobalString('PRESSING_DEFAULT_VAT', '20');
        $ht = $vat > 0 ? price2num($amountTtc / (1 + ($vat / 100))) : price2num($amountTtc);
        $lineRes = $invoice->addline($label, $ht, 1, $vat, 0, 0, 0, 0, 0, '', 0, 0, '', 'HT');
        if ($lineRes <= 0) {
            $this->error = $invoice->error;
            return -1;
        }

        if (!empty($conf->global->PRESSING_AUTOVALIDATE_BILLS)) {
            $invoice->validate($user);
        }

        return $invoice->id;
    }

    public function getNextNumRef()
    {
        return 'PR'.dol_print_date(dol_now(), '%Y%m%d%H%M%S');
    }
}
