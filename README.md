<h2>Conditions d'utilisation</h2>
<ul>
    <li>PHP 7.3</li>
    <li>Symfony 5</li>
    <li>Mysql 5.7.2</li>
    <li>Composer</li>
    <li>Sass</li>
</ul>

<h2>Doc pour les commits</h2>
:sparkles: <b>sparkles</b> nouvelles fonctionnalités
<br/>
:bug: <b>bug</b> correction des bugs
<br/>
:pencil: <b>pencil</b> écriture de documentation
<br/>
:lipstick: <b>lipstick</b> modifications visuelles
<br/>
:recycle: <b>recycle</b> refactoring de code (réorganisation du code etc)
<br/>
:pencil2: <b>pencil2</b> correction des typos

<h2>Commandes utiles</h2>
<h3>Mysql</h3>
<ul>
<li><a href="https://www.digitalocean.com/community/tutorials/comment-installer-mysql-sur-ubuntu-18-04-fr">Tuto pour installer Mysql</a></li>
<li>sudo apt-get install php7.4-mysql
</li>
<li>sudo /etc/init.d/mysql start</li>
</ul>
<h3>Créer la BD</h3>
<ul>
<li>php bin/console doctrine:database:create</li>
</ul>
<h3>Créer des entités</h3>
<ul>
<li>php bin/console make:entity</li>
<li>php bin/console make:migration</li>
<li>php bin/console doctrine:migrations:migrate</li>
<li>On peut ensuite supprimer les anciens fichiers des migrations dans le dossier src/Migrations</li>
</ul>
<h3>Symfony</h3>
<ul>
<li>php bin/console server:start</li>
</ul>
<h3>Fixtures</h3>
<ul>
<li>php bin/console doctrine:fixtures:load</li>
</ul>
<h3>Composer</h3>
<ul>
<li>which composer (pour localiser votre composer)</li>
<li>php7.3 -f {localisation_de_composer} update</li>
</ul>
