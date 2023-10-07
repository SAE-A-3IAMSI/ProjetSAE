Connexion Dolibarr:
admin
admin


Docker:

-Vérifier que Docker soit installé
-Clonez le dépôt github
-lancez les conteuneurs du projet avec un : docker compose up,
  (ajoutez un -d pour le lancer en arrière plan)
-Si vous voulez supprimez les conteneurs actifs, faites un : docker compose down

Une fois l'installation et le lancement du docker, ouvrez Dolibarr sur votre port (80:80 en localhost) puis faite ceci,
Cliquez sur Setup -> Modules/Applications , cochez :
-Stock
-API REST
Dans API REST activez le mode production.
Dans vos utilisateurs, modifier votre super-utilisateur et généré une clef API, et remplacez la clef des lignes commentées avec "clef api à changer" par la clef que vous venez de récupérer, puis relancer le projet.
