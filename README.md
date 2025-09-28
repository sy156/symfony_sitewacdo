Projet Wacdo – Ckecklist explicative du code non deployé, et non mis en production
___________________________________________________________________________________________
1️-Utilisateurs et rôles
___________________________________________________________________________________________
Trois utilisateurs créés via fixtures : SUPER_ADMIN, PREPARATEUR, ACCUEIL.

Mots de passe sécurisés avec hashage Symfony.

L’administrateur courant ne peut pas être supprimé.
___________________________________________________________________________________________
2️- Commandes (Order / OrderItem)
___________________________________________________________________________________________
Commandes fictives créées pour démontrer la gestion des rôles.

Chaque commande a des items liés aux produits via getReference.

Statuts différents : EN_COURS, EN_PREPARATION, LIVREE.

⚠️ Données de test uniquement, à ignorer en production réelle.
___________________________________________________________________________________________
3️- Authentification / sécurité
___________________________________________________________________________________________
Redirection automatique selon rôle après login.

CSRF token utilisé pour suppression d’utilisateurs et la validation des actions(servir, préparer, remettre au client etc..)

Firewalls et access_control configurés correctement dans security.yaml.
___________________________________________________________________________________________
4️- Templates
___________________________________________________________________________________________
Formulaires correctement affichés avec form->createView() qui permet au template twig de rendre une vue html correcte.

Boutons et actions respectent la sécurité et les rôles.

Messages flash pour feedback utilisateur pas utilisés partout, demande à être amélioré.

__________________________________________________________________________________________
5️-Notes importantes
__________________________________________________________________________________________

OrderFixtures et UserFixtures contiennent des données fictives pour la démonstration.

Les use inutilisés qui ont été laissés/peuvent être supprimés pour plus de propreté.

Petit projet fonctionnel et simpliste pour démonstration des fonctionnalités demandées.

Manque les photos des produits à ajouter dans le dossier public/images/product et à afficher dans twig.

Pas de création d'utilisateur externe, uniquement 3 user admin avec roles différents.
