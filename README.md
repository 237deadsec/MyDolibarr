# Module Dolibarr Pressing (TakePOS) - compatible Dolibarr 21.0.0

Module métier prêt au déploiement pour pressing:

- Gestion complète des commandes pressing dans le menu TakePOS.
- Configuration des services + prix directement côté TakePOS.
- Facturation simple avec **acompte** puis **solde au retrait**.
- Statuts opérationnels: Reçue, En traitement, Prête, Livrée.

## Contenu

- `pressing/core/modules/modPressing.class.php`: descripteur du module.
- `pressing/takepos/order_list.php`: liste des commandes.
- `pressing/takepos/order_card.php`: fiche commande + actions de facturation.
- `pressing/takepos/services.php`: onglet services/prix.
- `pressing/class/pressingorder.class.php`: logique métier commandes + factures.
- `pressing/class/pressingservice.class.php`: CRUD services pressing.
- `pressing/sql/*`: schéma SQL complet.

## Installation (ZIP dans Dolibarr 21)

1. Sur votre environnement Dolibarr, placez-vous dans `htdocs/custom/`.
2. Dézippez pour obtenir le dossier `pressing/`.
3. Importez les scripts SQL `llx_pressing_order.sql` et `llx_c_pressing_service.sql`.
4. Activez le module **Pressing** dans *Configuration > Modules*.
5. Donnez les droits utilisateurs (lecture/écriture/facturation/config).

## Packaging ZIP

Depuis un serveur où le dossier `pressing` est présent:

```bash
cd htdocs/custom
zip -r pressing-dolibarr21.zip pressing
```

## Flux métier recommandé

1. Créer commande pressing (TakePOS > Pressing > Nouvelle commande).
2. Ajouter les lignes de prestations (service, quantité, prix).
3. Saisir un acompte si besoin, puis créer facture d'acompte.
4. Marquer "prêt au retrait".
5. Au retrait, créer facture de solde.

