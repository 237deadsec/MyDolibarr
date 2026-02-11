<?php
require '../../../main.inc.php';

$langs->loadLangs(array('pressing@pressing'));
if (empty($user->rights->pressing->read)) accessforbidden();

$statusLabel = array(
    0 => $langs->trans('PressingStatusReceived'),
    1 => $langs->trans('PressingStatusProcessing'),
    2 => $langs->trans('PressingStatusReady'),
    3 => $langs->trans('PressingStatusDelivered'),
);

$sql = 'SELECT rowid, ref, fk_soc, status, date_reception, date_due, total_ttc, deposit_ttc, fk_facture_deposit, fk_facture_final';
$sql .= ' FROM '.MAIN_DB_PREFIX.'pressing_order';
$sql .= ' WHERE entity IN ('.getEntity('pressing_order').') ORDER BY rowid DESC';
$resql = $db->query($sql);

llxHeader('', $langs->trans('PressingOrders'));
print load_fiche_titre($langs->trans('PressingOrders'));
print '<div class="tabsAction"><a class="butAction" href="order_card.php?action=create">'.$langs->trans('NewPressingOrder').'</a></div>';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><th>'.$langs->trans('Ref').'</th><th>'.$langs->trans('ThirdParty').'</th><th>'.$langs->trans('Status').'</th><th>'.$langs->trans('DateReception').'</th><th>'.$langs->trans('DateDue').'</th><th class="right">'.$langs->trans('AmountTTC').'</th><th class="right">'.$langs->trans('Deposit').'</th><th>'.$langs->trans('Bills').'</th></tr>';

if ($resql) {
    while ($obj = $db->fetch_object($resql)) {
        print '<tr class="oddeven">';
        print '<td><a href="order_card.php?id='.$obj->rowid.'">'.$obj->ref.'</a></td>';
        print '<td>'.(int) $obj->fk_soc.'</td>';
        print '<td>'.(isset($statusLabel[(int) $obj->status]) ? $statusLabel[(int) $obj->status] : $obj->status).'</td>';
        print '<td>'.dol_print_date($db->jdate($obj->date_reception), 'dayhour').'</td>';
        print '<td>'.dol_print_date($db->jdate($obj->date_due), 'dayhour').'</td>';
        print '<td class="right">'.price($obj->total_ttc).'</td>';
        print '<td class="right">'.price($obj->deposit_ttc).'</td>';
        print '<td>D: '.(!empty($obj->fk_facture_deposit) ? $obj->fk_facture_deposit : '-').' / F: '.(!empty($obj->fk_facture_final) ? $obj->fk_facture_final : '-').'</td>';
        print '</tr>';
    }
}
print '</table>';

llxFooter();
$db->close();
