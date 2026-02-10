<?php
require '../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/custom/pressing/class/pressingservice.class.php';

$langs->loadLangs(array('pressing@pressing'));
if (empty($user->rights->pressing->config)) accessforbidden();

$action = GETPOST('action', 'aZ09');
$repo = new PressingService($db);

if ($action === 'save') {
    $res = $repo->upsert(GETPOSTINT('id'), GETPOST('ref', 'aZ09'), GETPOST('label', 'alphanohtml'), GETPOST('price_ttc', 'alpha'), GETPOST('default_qty', 'alpha'), GETPOSTINT('active'));
    if ($res > 0) setEventMessages($langs->trans('Saved'), null, 'mesgs'); else setEventMessages($langs->trans('Error'), null, 'errors');
}
if ($action === 'delete') {
    $res = $repo->delete(GETPOSTINT('id'));
    if ($res > 0) setEventMessages($langs->trans('Deleted'), null, 'mesgs'); else setEventMessages($langs->trans('Error'), null, 'errors');
}

$services = $repo->fetchAll(false);
llxHeader('', $langs->trans('PressingServicesAndPrices'));
print load_fiche_titre($langs->trans('PressingServicesAndPrices'));

print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'"><input type="hidden" name="token" value="'.newToken().'"><input type="hidden" name="action" value="save">';
print '<table class="border centpercent">';
print '<tr><td>'.$langs->trans('Code').'</td><td><input name="ref" required></td><td>'.$langs->trans('Label').'</td><td><input name="label" required></td></tr>';
print '<tr><td>'.$langs->trans('UnitPriceTTC').'</td><td><input name="price_ttc" required></td><td>'.$langs->trans('Qty').'</td><td><input name="default_qty" value="1"></td></tr>';
print '<tr><td>'.$langs->trans('Active').'</td><td><input type="checkbox" name="active" value="1" checked></td><td colspan="2"><input class="button" type="submit" value="'.$langs->trans('Add').'"/></td></tr>';
print '</table></form><br>';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><th>'.$langs->trans('Code').'</th><th>'.$langs->trans('Label').'</th><th>'.$langs->trans('Qty').'</th><th>'.$langs->trans('UnitPriceTTC').'</th><th>'.$langs->trans('Status').'</th><th>'.$langs->trans('Action').'</th></tr>';
foreach ($services as $s) {
    print '<tr class="oddeven"><td>'.$s->ref.'</td><td>'.$s->label.'</td><td>'.$s->default_qty.'</td><td>'.price($s->price_ttc).'</td><td>'.($s->active ? $langs->trans('Enabled') : $langs->trans('Disabled')).'</td><td><a href="'.$_SERVER['PHP_SELF'].'?action=delete&id='.$s->rowid.'">'.$langs->trans('Delete').'</a></td></tr>';
}
print '</table>';

llxFooter();
$db->close();
