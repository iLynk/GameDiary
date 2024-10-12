# 🎮 ! GAME DIARY ! 🎮

## Si vous voulez essayer l'application chez vous, voici les étapes 

1. `git clone 'https://github.com/iLynk/GameDiary.git'`
2. `composer update`
3. `php bin/console doctrine:database:create`
4. `php bin/console doctrine:migration:migrate --no-interaction`
5. `php bin/console doctrine:fixtures:load --no-interaction`
6. `php bin/console cache:clear`

## 🚀 **Lancer le serveur** 

```bash
symfony serve:start
```
## 🕹️ Connectez-vous en administrateur et appuyez sur les différents boutons afin de d'alimenter votre base de données

Respectivement, les boutons font des requêtes fetch() à ces routes
- `/getCategories`
- `/getPlatforms`
- `/getGameCovers`
- `/getGames`

## 👥 Vous pouvez maintenant utiliser les comptes de tests pour vous balader sur l'application

```
👑 ADMIN :
        Email : milhan@gmail.com
        Pass : milhan33

🔧 MODERATEUR :
        Email : salome@gmail.com
        Pass : salome33

👤 UTILISATEUR :
        Email : valerie@gmail.com
        Pass : valerie33
```

## Partie Jeux 

- Vous pouvez vous balader et rechercher votre jeu préféré grace au champ de recherche dynamique ou en utilisant les différents filtres présents
- Vous pouvez vous amuser à ajouter des jeux à votre liste de "favoris"
- Vous pouvez également rédiger des avis sur ces derniers, que vous pourrez retrouver dans votre onglet "Profil"
- Vous pouvez évaluer les avis rédigés par d'autres utilisateurs (de base, certains jeux ont des avis générés avec les fixtures)

## Partie Profil

- Vous pouvez vous amuser à changer vos informations ainsi que votre photo de profil, ATTENTION, seules les images PNG/JPG/JPEG sont autorisées avec un poids =< 10mo !
- Vous pouvez retrouver, modifier, supprimer les différents avis que vous avez rédigé, chaque utilisateur devrait de base avoir des avis rédigés grâce aux fixtures
- Vous pouvez également supprimer les jeux présents dans votre liste de favoris afin de pourquoi pas faire un tri !
