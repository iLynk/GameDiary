Bienvenue à tous sur mon projet Game Diary !

Si jamais vous voulez essayer l'application chez vous, voici la liste des choses à faire :

1 - git clone 'https://github.com/iLynk/GameDiary.git'
2 - composer update
3 - php bin/console doctrine:database:create 
    php bin/console doctrine:migration:migrate --no-interaction
    php bin/console doctrine:fixtures:load --no-interaction
    php bin/console cache:clear

Après avoir effectué ces 3 premières opérations, vous pouvez lancer votre serveur, 
    symfony serve:start -d (le -d est utilisé pour pouvoir continuer à utiliser le temrinal sur lequel est lancé le serveur)

Maintenant sur la page d'accueil, je vous invite à aller aux URLs suivantes : 
    /getCategories
    /getGameCovers
    /getGames
qui va permettre d'alimenter la base de données des jeux récoltés via l'API IGDB.
vous avez à votre disposition 3 comptes afin de tester les différents rôles : 
ADMIN : 
    email : milhan@gmail.com
    pass : milhan33
    
MODERATEUR :
    email : salome@gmail.com 
    pass : salome33

UTILISATEUR :
    email : valerie@gmail.com
    pass : valerie33

Je vous invite désormais à vous balader sur l'application, aller fouiller l'onglet "Mon profil" qui change en fonction du rôle, vous pouvez modifier les informations à votre guise etc...
