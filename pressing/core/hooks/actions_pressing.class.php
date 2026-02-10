<?php
require_once DOL_DOCUMENT_ROOT.'/custom/pressing/class/pressingorder.class.php';

class ActionsPressing
{
    public $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager)
    {
        global $langs, $user;

        if (strpos((string) $parameters['context'], 'takeposinvoicecard') === false) {
            return 0;
        }
        if (empty($user->rights->pressing->write) || empty($object->id) || empty($object->socid)) {
            return 0;
        }

        if (GETPOST('action', 'aZ09') === 'createpressingfrominvoice') {
            $order = new PressingOrder($this->db);
            $order->entity = getEntity('pressing_order');
            $order->fk_soc = (int) $object->socid;
            $order->status = 0;
            $order->note_private = 'Créée depuis ticket TakePOS #'.$object->id;
            $order->date_reception = dol_now();
            $order->date_due = dol_time_plus_duree(dol_now(), getDolGlobalString('PRESSING_DEFAULT_DELAI', '48'), 'h');
            $order->total_ttc = (float) $object->total_ttc;
            $order->deposit_ttc = 0;

            $order->lines = array(array(
                'fk_service' => 0,
                'description' => 'Prestations pressing depuis ticket TakePOS #'.$object->id,
                'qty' => 1,
                'unit_price_ttc' => (float) $object->total_ttc,
            ));

            $res = $order->create($user);
            if ($res > 0) {
                header('Location: '.DOL_URL_ROOT.'/custom/pressing/takepos/order_card.php?id='.$res);
                exit;
            }
            setEventMessages($order->error, null, 'errors');
        }

        print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=createpressingfrominvoice">';
        print $langs->trans('CreatePressingOrder');
        print '</a>';

        return 0;
    }
}
