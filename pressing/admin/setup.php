<?php
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';

$langs->loadLangs(array('admin', 'pressing@pressing'));
if (!$user->admin) {
    accessforbidden();
}

$action = GETPOST('action', 'aZ09');
if ($action === 'save') {
    dolibarr_set_const($db, 'PRESSING_DEFAULT_DELAI', GETPOSTINT('default_delay'), 'chaine', 0, '', $conf->entity);
    dolibarr_set_const($db, 'PRESSING_DEFAULT_VAT', GETPOST('default_vat', 'alpha'), 'chaine', 0, '', $conf->entity);
    dolibarr_set_const($db, 'PRESSING_AUTOVALIDATE_BILLS', GETPOSTINT('autovalidate'), 'yesno', 0, '', $conf->entity);
    setEventMessages($langs->trans('SetupSaved'), null, 'mesgs');
}

llxHeader('', $langs->trans('PressingSetup'));
print load_fiche_titre($langs->trans('PressingSetup'));

print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="save">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre"><td>'.$langs->trans('Parameter').'</td><td>'.$langs->trans('Value').'</td></tr>';
print '<tr><td>'.$langs->trans('DefaultPressingDelayHours').'</td><td><input type="number" name="default_delay" value="'.getDolGlobalInt('PRESSING_DEFAULT_DELAI', 48).'"></td></tr>';
print '<tr><td>'.$langs->trans('DefaultVAT').'</td><td><input type="text" name="default_vat" value="'.getDolGlobalString('PRESSING_DEFAULT_VAT', '20').'"></td></tr>';
print '<tr><td>'.$langs->trans('AutoValidateInvoice').'</td><td><input type="checkbox" name="autovalidate" value="1" '.(getDolGlobalInt('PRESSING_AUTOVALIDATE_BILLS', 1) ? 'checked' : '').'></td></tr>';
print '</table>';
print '<div class="center"><input type="submit" class="button" value="'.$langs->trans('Save').'"/></div>';
print '</form>';

print '<br><div class="info">'.$langs->trans('PressingZipInfo').': <code>cd htdocs/custom && zip -r pressing-dolibarr21.zip pressing</code></div>';

llxFooter();
$db->close();
