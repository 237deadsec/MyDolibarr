<?php
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

class modPressing extends DolibarrModules
{
    public function __construct($db)
    {
        $this->db = $db;
        $this->numero = 106500;
        $this->rights_class = 'pressing';

        $this->family = 'special';
        $this->module_position = '500';
        $this->name = preg_replace('/^mod/i', '', get_class($this));
        $this->description = 'Pressing complet pour Dolibarr 21 + TakePOS (commandes, acompte, retrait, facturation)';
        $this->version = '1.2.0';
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
        $this->picto = 'washingmachine@pressing';

        $this->module_parts = array(
            'hooks' => array('takeposinvoicecard'),
            'triggers' => 1,
        );

        $this->dirs = array('/pressing/temp');
        $this->config_page_url = array('setup.php@pressing');
        $this->depends = array('modFacture', 'modTakePos');
        $this->langfiles = array('pressing@pressing');

        $this->phpmin = array(7, 4);
        $this->need_dolibarr_version = array(21, 0);

        $this->const = array(
            array('PRESSING_DEFAULT_DELAI', 'chaine', '48', 'Delai de disponibilite en heures'),
            array('PRESSING_DEFAULT_VAT', 'chaine', '20', 'TVA par defaut sur prestations pressing'),
            array('PRESSING_AUTOVALIDATE_BILLS', 'yesno', '1', 'Valider auto les factures d acompte et solde'),
        );

        $r = 0;
        $this->rights = array();
        $this->rights[$r][0] = 106501; $this->rights[$r][1] = 'Lire commandes pressing'; $this->rights[$r][4] = 'read'; $r++;
        $this->rights[$r][0] = 106502; $this->rights[$r][1] = 'Creer/modifier commandes pressing'; $this->rights[$r][4] = 'write'; $r++;
        $this->rights[$r][0] = 106503; $this->rights[$r][1] = 'Facturer commandes pressing'; $this->rights[$r][4] = 'invoice'; $r++;
        $this->rights[$r][0] = 106504; $this->rights[$r][1] = 'Configurer services et prix pressing'; $this->rights[$r][4] = 'config';

        $this->menu = array();
        $this->menu[] = array(
            'fk_menu' => 'fk_mainmenu=takepos', 'type' => 'left', 'titre' => 'PressingOrders',
            'mainmenu' => 'takepos', 'leftmenu' => 'pressing_orders', 'url' => '/pressing/takepos/order_list.php',
            'langs' => 'pressing@pressing', 'position' => 120, 'enabled' => '$conf->pressing->enabled',
            'perms' => '$user->rights->pressing->read', 'target' => '', 'user' => 2,
        );
        $this->menu[] = array(
            'fk_menu' => 'fk_mainmenu=takepos,fk_leftmenu=pressing_orders', 'type' => 'left', 'titre' => 'NewPressingOrder',
            'mainmenu' => 'takepos', 'leftmenu' => 'pressing_order_new', 'url' => '/pressing/takepos/order_card.php?action=create',
            'langs' => 'pressing@pressing', 'position' => 121, 'enabled' => '$conf->pressing->enabled',
            'perms' => '$user->rights->pressing->write', 'target' => '', 'user' => 2,
        );
        $this->menu[] = array(
            'fk_menu' => 'fk_mainmenu=takepos,fk_leftmenu=pressing_orders', 'type' => 'left', 'titre' => 'PressingServicesAndPrices',
            'mainmenu' => 'takepos', 'leftmenu' => 'pressing_services', 'url' => '/pressing/takepos/services.php',
            'langs' => 'pressing@pressing', 'position' => 122, 'enabled' => '$conf->pressing->enabled',
            'perms' => '$user->rights->pressing->config', 'target' => '', 'user' => 2,
        );

        $this->dictionaries = array(
            'langs' => 'pressing@pressing',
            'tabname' => array(MAIN_DB_PREFIX.'c_pressing_service'),
            'tablib' => array('PressingService'),
            'tabsql' => array('SELECT f.rowid as rowid, f.ref, f.label, f.default_qty, f.price_ttc, f.active FROM '.MAIN_DB_PREFIX.'c_pressing_service as f'),
            'tabsqlsort' => array('f.label ASC'),
            'tabfield' => array('ref,label,default_qty,price_ttc,active'),
            'tabfieldvalue' => array('ref,label,default_qty,price_ttc,active'),
            'tabfieldinsert' => array('entity,ref,label,default_qty,price_ttc,active'),
            'tabrowid' => array('rowid'),
            'tabcond' => array('$conf->pressing->enabled'),
        );
    }

    public function init($options = '')
    {
        $result = $this->_load_tables('/pressing/sql/');
        if ($result < 0) {
            return -1;
        }
        return $this->_init(array(), $options);
    }

    public function remove($options = '')
    {
        return $this->_remove(array(), $options);
    }
}
