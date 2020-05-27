# Drupal builder

## Actions dispo

- Création du docker
- Installation de drupal
- Installation de module tiers.


# Comment faire ?
1. Ajouter le repo : 
`composer global config repositories.drupal-builder vcs https://github.com/tsecher/drupal-builder`
Vous aurez peut-êter aussi besoin d'ajouter
`composer global config repositories.bim-runner vcs https://github.com/tsecher/bim-runner`
Si il y a des problèmes de stability :
 `composer global config minimum-stability dev`
 et 
 `composer global config prefer-stable true`

2. Require global
`composer global require lycanthrop/drupal-builder`
3. Lancer l'action `drupal-builder` dans le répertoire contenant votre projet et laisser vous porter.

