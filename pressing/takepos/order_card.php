<?php
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/pressing/class/pressingorder.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/pressing/class/pressingservice.class.php';

$langs->loadLangs(array('pressing@pressing', 'companies', 'bills'));
$action = GETPOST('action', 'aZ09');
$id = GETPOSTINT('id');
if (empty($user->rights->pressing->read)) accessforbidden();

$object = new PressingOrder($db);
$serviceRepo = new PressingService($db);
$services = $serviceRepo->fetchAll(true);

if ($action === 'add' && !empty($user->rights->pressing->write)) {
    $object->entity = $conf->entity;
    $object->fk_soc = GETPOSTINT('fk_soc');
    $object->status = 0;
    $object->note_private = GETPOST('note_private', 'restricthtml');
    $object->date_reception = dol_now();
    $object->date_due = dol_time_plus_duree(dol_now(), GETPOSTINT('delay_h') ?: getDolGlobalInt('PRESSING_DEFAULT_DELAI', 48), 'h');
    $object->deposit_ttc = price2num(GETPOST('deposit_ttc', 'alpha'));

    $serviceIds = GETPOST('service_id', 'array');
    $qtys = GETPOST('qty', 'array');
    $prices = GETPOST('unit_price_ttc', 'array');
    $descs = GETPOST('description', 'array');

    $totalTtc = 0;
    foreach ((array) $serviceIds as $k => $sid) {
        $qty = price2num($qtys[$k]);
        $unit = price2num($prices[$k]);
        if ($qty <= 0 || $unit < 0) continue;
        $object->lines[] = array(
            'fk_service' => (int) $sid,
            'description' => $descs[$k],
            'qty' => $qty,
            'unit_price_ttc' => $unit,
        );
        $totalTtc += ($qty * $unit);
    }
    $object->total_ttc = price2num($totalTtc);

    $res = $object->create($user);
    if ($res > 0) {
        header('Location: '.$_SERVER['PHP_SELF'].'?id='.$res);
        exit;
    }
    setEventMessages($object->error, null, 'errors');
}

if ($id > 0) $object->fetch($id);

if ($action === 'markready' && !empty($user->rights->pressing->write) && !empty($object->id)) {
    $object->markReady();
    header('Location: '.$_SERVER['PHP_SELF'].'?id='.$object->id); exit;
}
if ($action === 'depositinvoice' && !empty($user->rights->pressing->invoice) && !empty($object->id)) {
    $amount = GETPOST('amount_ttc', 'alpha');
    $res = $object->createDepositInvoice($user, $amount ?: $object->deposit_ttc);
    if ($res > 0) setEventMessages($langs->trans('DepositInvoiceCreated').' #'.$res, null, 'mesgs'); else setEventMessages($object->error, null, 'errors');
    $object->fetch($id);
}
if ($action === 'pickupinvoice' && !empty($user->rights->pressing->invoice) && !empty($object->id)) {
    $res = $object->createFinalInvoice($user);
    if ($res > 0) setEventMessages($langs->trans('PickupInvoiceCreated').' #'.$res, null, 'mesgs'); else setEventMessages($object->error, null, 'errors');
    $object->fetch($id);
}

llxHeader('', $langs->trans('PressingOrder'));

if ($action === 'create') {
    print load_fiche_titre($langs->trans('NewPressingOrder'));
    print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
    print '<input type="hidden" name="token" value="'.newToken().'">';
    print '<input type="hidden" name="action" value="add">';
    print '<table class="border centpercent">';
    print '<tr><td>'.$langs->trans('ThirdParty').'</td><td><input type="number" name="fk_soc" required></td></tr>';
    print '<tr><td>'.$langs->trans('DefaultPressingDelayHours').'</td><td><input type="number" name="delay_h" value="'.getDolGlobalInt('PRESSING_DEFAULT_DELAI', 48).'"></td></tr>';
    print '<tr><td>'.$langs->trans('Deposit').'</td><td><input type="text" name="deposit_ttc" value="0"></td></tr>';
    print '<tr><td>'.$langs->trans('NotePrivate').'</td><td><textarea name="note_private"></textarea></td></tr>';
    print '</table><br>';

    print '<table class="noborder centpercent">';
    print '<tr class="liste_titre"><th>'.$langs->trans('Service').'</th><th>'.$langs->trans('Description').'</th><th>'.$langs->trans('Qty').'</th><th>'.$langs->trans('UnitPriceTTC').'</th></tr>';
    for ($i = 0; $i < 6; $i++) {
        print '<tr class="oddeven"><td><select name="service_id[]"><option value="0">-</option>';
        foreach ($services as $s) print '<option value="'.$s->rowid.'">'.$s->label.'</option>';
        print '</select></td><td><input type="text" name="description[]"></td><td><input type="text" name="qty[]" value="1"></td><td><input type="text" name="unit_price_ttc[]" value="0"></td></tr>';
    }
    print '</table>';
    print '<div class="center"><input class="button" type="submit" value="'.$langs->trans('Create').'"/></div></form>';
} elseif (!empty($object->id)) {
    $remaining = price2num($object->total_ttc - $object->deposit_ttc);
    print load_fiche_titre($langs->trans('PressingOrder').' '.$object->ref);
    print '<table class="border centpercent">';
    print '<tr><td>'.$langs->trans('Ref').'</td><td>'.$object->ref.'</td></tr>';
    print '<tr><td>'.$langs->trans('ThirdParty').'</td><td>'.$object->fk_soc.'</td></tr>';
    print '<tr><td>'.$langs->trans('Status').'</td><td>'.(int) $object->status.'</td></tr>';
    print '<tr><td>'.$langs->trans('AmountTTC').'</td><td>'.price($object->total_ttc).'</td></tr>';
    print '<tr><td>'.$langs->trans('Deposit').'</td><td>'.price($object->deposit_ttc).'</td></tr>';
    print '<tr><td>'.$langs->trans('RemainingToPay').'</td><td>'.price($remaining).'</td></tr>';
    print '<tr><td>'.$langs->trans('DateReception').'</td><td>'.dol_print_date($object->date_reception, 'dayhour').'</td></tr>';
    print '<tr><td>'.$langs->trans('DateDue').'</td><td>'.dol_print_date($object->date_due, 'dayhour').'</td></tr>';
    print '<tr><td>'.$langs->trans('DepositInvoice').'</td><td>'.($object->fk_facture_deposit ?: '-').'</td></tr>';
    print '<tr><td>'.$langs->trans('PickupInvoice').'</td><td>'.($object->fk_facture_final ?: '-').'</td></tr>';
    print '</table>';

    print '<h3>'.$langs->trans('Services').'</h3><table class="noborder centpercent"><tr class="liste_titre"><th>'.$langs->trans('Description').'</th><th>'.$langs->trans('Qty').'</th><th>'.$langs->trans('UnitPriceTTC').'</th><th>'.$langs->trans('AmountTTC').'</th></tr>';
    foreach ($object->lines as $line) {
        print '<tr class="oddeven"><td>'.$line['description'].'</td><td>'.$line['qty'].'</td><td>'.price($line['unit_price_ttc']).'</td><td>'.price($line['total_ttc']).'</td></tr>';
    }
    print '</table>';

    print '<div class="tabsAction">';
    if ((int) $object->status < 2 && !empty($user->rights->pressing->write)) print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=markready">'.$langs->trans('MarkAsReady').'</a>';
    if (empty($object->fk_facture_deposit) && !empty($user->rights->pressing->invoice)) print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=depositinvoice&amount_ttc='.(float)$object->deposit_ttc.'">'.$langs->trans('CreateDepositInvoice').'</a>';
    if (empty($object->fk_facture_final) && !empty($user->rights->pressing->invoice)) print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=pickupinvoice">'.$langs->trans('CreatePickupInvoice').'</a>';
    print '</div>';
}

llxFooter();
$db->close();
