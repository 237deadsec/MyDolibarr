# Module Dolibarr Pressing (TakePOS) - compatible Dolibarr 21.0.0

Module métier prêt au déploiement pour pressing:

- Gestion complète des commandes pressing dans le menu TakePOS.
- Configuration des services + prix directement côté TakePOS.
- Facturation simple avec **acompte** puis **solde au retrait**.
- Statuts opérationnels: Reçue, En traitement, Prête, Livrée.

## Structure fournie

Le module est dans le dossier `pressing/` avec la structure attendue par Dolibarr.

- `pressing/core/modules/modPressing.class.php`: descripteur du module.
- `pressing/takepos/order_list.php`: liste des commandes.
- `pressing/takepos/order_card.php`: fiche commande + actions de facturation.
- `pressing/takepos/services.php`: onglet services/prix.
- `pressing/class/pressingorder.class.php`: logique métier commandes + factures.
- `pressing/class/pressingservice.class.php`: CRUD services pressing.
- `pressing/sql/*`: schéma SQL complet.

## Installation ZIP (sans erreur de format)

> Important: dans le zip, le dossier racine doit être **`pressing/`** (ou `htdocs/pressing/`).
> Ne zippez PAS seulement le contenu interne du dossier.

### Méthode conseillée (depuis ce dépôt)

```bash
./build/make_zip.sh
```

Cela génère:
- `dist/pressing-dolibarr21.zip` (racine `pressing/`) ✅
- `dist/pressing-dolibarr21-htdocs.zip` (racine `htdocs/pressing/`) ✅

### Déploiement dans Dolibarr

1. Dolibarr > **Configuration > Modules/Applications > Déployer/Installer un module externe**.
2. Importez `dist/pressing-dolibarr21.zip`.
3. Activez le module **Pressing**.
4. Donnez les droits utilisateurs (lecture/écriture/facturation/config).

## Flux métier recommandé

1. Créer commande pressing (TakePOS > Pressing > Nouvelle commande).
2. Ajouter les lignes de prestations (service, quantité, prix).
3. Saisir un acompte si besoin, puis créer facture d'acompte.
4. Marquer "prêt au retrait".
5. Au retrait, créer facture de solde.
