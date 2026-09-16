<nav><ul>
<li><a href='index.php'>Accueil</a></li>
<?php if ($_SESSION['statut'] === 'admin'): ?>
<li><a href='insertion.php'>Insérer</a></li>
<li><a href='modification.php'>Modifier</a></li>
<li><a href='suppression.php'>Supprimer</a></li>
<?php endif; ?>
<li><a href='deconnexion.php'>Déconnexion</a></li>
</ul></nav>