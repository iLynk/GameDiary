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
## 🕹️ Accéder aux URLs dans l'ordre suivant pour alimenter la base de données 

- `/getCategories`
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
