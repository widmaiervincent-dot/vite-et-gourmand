<?php
// Les sessions sont enregistrées dans MySQL (table session_php) au lieu de fichiers sur le serveur.
// Pourquoi : sur fly.io la machine s'arrête quand personne ne visite le site et ses fichiers sont effacés,
// donc avec les sessions par défaut de PHP tout le monde était déconnecté à chaque redémarrage.

class SessionBdd implements SessionHandlerInterface
{
    private $bdd;

    public function __construct($bdd)
    {
        $this->bdd = $bdd;
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    // PHP appelle read() au session_start() : on renvoie les données de la session (ou vide si elle n'existe pas)
    public function read(string $id): string|false
    {
        $requete = $this->bdd->prepare('SELECT donnees FROM session_php WHERE id = ?');
        $requete->execute([$id]);
        $donnees = $requete->fetchColumn();
        return $donnees === false ? '' : $donnees;
    }

    // PHP appelle write() à la fin de la page : on crée ou on met à jour la ligne
    public function write(string $id, string $data): bool
    {
        $requete = $this->bdd->prepare('REPLACE INTO session_php (id, donnees, date_maj) VALUES (?, ?, ?)');
        return $requete->execute([$id, $data, time()]);
    }

    // déconnexion : on supprime la ligne
    public function destroy(string $id): bool
    {
        return $this->bdd->prepare('DELETE FROM session_php WHERE id = ?')->execute([$id]);
    }

    // nettoyage des vieilles sessions (PHP l'appelle de temps en temps)
    public function gc(int $max_lifetime): int|false
    {
        $requete = $this->bdd->prepare('DELETE FROM session_php WHERE date_maj < ?');
        $requete->execute([time() - $max_lifetime]);
        return $requete->rowCount();
    }
}
