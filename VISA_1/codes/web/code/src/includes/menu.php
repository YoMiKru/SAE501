<nav><ul>
<li><a href='/index.php'>Accueil</a></li>
<?php if ($_SESSION['statut'] === 'admin'): ?>
<li><a href='/pages/add_product.php'>Insérer</a></li>
<li><a href='/pages/edit_product.php'>Modifier</a></li>
<li><a href='/pages/delete_product.php'>Supprimer</a></li>
<?php endif; ?>
<li><a href='/pages/logout.php'>Déconnexion</a></li>
</ul></nav>