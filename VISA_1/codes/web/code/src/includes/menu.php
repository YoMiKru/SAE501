<nav><ul>
<li><a href='/var/www/html/index.php'>Accueil</a></li>
<?php if ($_SESSION['statut'] === 'admin'): ?>
<li><a href='/var/www/html/pages/add_product.php'>Insérer</a></li>
<li><a href='/var/www/html/pages/edit_product.php'>Modifier</a></li>
<li><a href='/var/www/html/pages/delete_product.php'>Supprimer</a></li>
<?php endif; ?>
<li><a href='/var/www/html/pages/logout.php'>Déconnexion</a></li>
</ul></nav>